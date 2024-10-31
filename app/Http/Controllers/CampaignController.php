<?php

declare(strict_types=1);

namespace ParityPress\Http\Controllers;

use ParityPress\Data\CampaignData;
use ParityPress\Framework\Http\Controller;
use ParityPress\Http\Requests\CampaignFormRequest;
use ParityPress\Http\Resources\CampaignResource;
use ParityPress\Services\CampaignService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class CampaignController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = parity_press(CampaignService::class);
    }

    public function middleware()
    {
        if (!parity_press_is_admin()) {
            return new WP_Error('rest_forbidden', __('You are not allowed to access this endpoint', 'parity-press'), [
                'status' => 401
            ]);
        }

        return true;
    }

    public function index(): WP_REST_Response
    {
        return CampaignResource::collection($this->service->all());
    }

    public function store(WP_REST_Request $request): WP_REST_Response
    {
        $formRequest = new CampaignFormRequest($request);

        if (!$formRequest->validate()) {
            return new WP_Error('validation_failed', 'Validation failed', [
                'status' => 400,
                'errors' => $formRequest->getErrors(),
            ]);
        }

        $data = $formRequest->validated();

        $data = CampaignData::sanitize($data);

        $this->service->create($data);

        return new WP_REST_Response(['message' => __('Campaign created successfully', 'parity-press')]);
    }

    public function show(WP_REST_Request $request): WP_REST_Response
    {
        $id = (int) $request->get_param('id');
        $campaign = $this->service->findById($id);

        if (!$campaign) {
            return new WP_REST_Response(['message' => __('Campaign not found', 'parity-press')], 404);
        }

        return CampaignResource::make($campaign);
    }

    public function update(WP_REST_Request $request)
    {
        $formRequest = new CampaignFormRequest($request);

        if (!$formRequest->validate()) {
            return new WP_Error('validation_failed', 'Validation failed', [
                'status' => 400,
                'errors' => $formRequest->getErrors(),
            ]);
        }

        $data = $formRequest->validated();

        $id = (int) $request->get_param('id');

        $data = CampaignData::sanitize($data);

        $this->service->update($id, $data);

        return new WP_REST_Response(['message' => __('Campaign updated successfully', 'parity-press')]);
    }

    public function destroy(WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');

        $this->service->delete($id);

        return new WP_REST_Response(['message' => __('Campaign deleted successfully', 'parity-press')]);
    }
}
