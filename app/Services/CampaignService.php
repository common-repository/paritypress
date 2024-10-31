<?php

declare(strict_types=1);

namespace ParityPress\Services;

class CampaignService
{
    public function all(): array
    {
        $campaigns = get_posts([
            'post_type'         => 'parity_campaign',
            'posts_per_page'    => -1,
        ]);

        $campaigns = array_map(function ($campaign) {
            foreach ($this->getPostMeta($campaign->ID) as $key => $value) {
                $campaign->{$key} = $value;
            }

            $campaign->title = $campaign->post_title;
            $campaign->description = $campaign->post_content;

            return $campaign;
        }, $campaigns);

        return $campaigns;
    }

    public function findById(int $id): ?object
    {
        $campaign = get_post($id);

        if (!$campaign || $campaign->post_type !== 'parity_campaign') {
            return null;
        }

        foreach ($this->getPostMeta($campaign->ID) as $key => $value) {
            $campaign->{$key} = $value;
        }

        $campaign->title = $campaign->post_title;
        $campaign->description = $campaign->post_content;

        return $campaign;
    }

    public function create(array $data): int
    {
        $campaignId = wp_insert_post([
            'post_type'     => 'parity_campaign',
            'post_title'    => $data['title'],
            'post_content'  => $data['description'],
            'post_status'   => 'publish',
        ]);

        $this->updatePostMeta($campaignId, $data);

        return $campaignId;
    }

    public function update(int $id, array $data): int
    {
        $campaignId = wp_update_post([
            'ID'            => $id,
            'post_title'    => $data['title'],
            'post_content'  => $data['description'],
        ]);

        $this->updatePostMeta($id, $data);

        return $campaignId;
    }

    public function delete(int $id)
    {
        return wp_delete_post($id, true);
    }

    public function getPostMeta(int $id): array
    {
        return [
            'discount_text'     => (string) get_post_meta($id, 'parity_campaign_discount_text', true),
            'discounts'         => (array) get_post_meta($id, 'parity_campaign_discounts', true),
            'customizations'    => (array) get_post_meta($id, 'parity_campaign_customizations', true),
            'start_date'        => (string) get_post_meta($id, 'parity_campaign_start_date', true),
            'end_date'          => (string) get_post_meta($id, 'parity_campaign_end_date', true),
        ];
    }

    public function updatePostMeta(int $id, array $data): void
    {
        update_post_meta($id, 'parity_campaign_discount_text', (string) $data['discount_text']);
        update_post_meta($id, 'parity_campaign_discounts', (array) $data['discounts']);
        update_post_meta($id, 'parity_campaign_customizations', (array) $data['customizations']);
        update_post_meta($id, 'parity_campaign_start_date', (string) $data['start_date']);
        update_post_meta($id, 'parity_campaign_end_date', (string) $data['end_date']);
    }

    public function getActiveCampaign(): ?object
    {
        $campaigns = $this->all();

        $activeCampaign = array_filter($campaigns, function ($campaign) {
            $startDate = strtotime($campaign->start_date);
            $endDate = strtotime($campaign->end_date);

            if (!$startDate || !$endDate) {
                return true;
            }

            $now = strtotime('now');

            return $now >= $startDate && $now <= $endDate;
        });

        return array_shift($activeCampaign);
    }
}
