<?php

declare(strict_types=1);

namespace ParityPress\Services;

class TemplateService
{
    private array $replacements = [];

    public function replace($key, $value): self
    {
        $this->replacements[$key] = $value;

        return $this;
    }

    public function replacements(array $replacements): self
    {
        foreach ($replacements as $key => $value) {
            $this->replace($key, $value);
        }

        return $this;
    }

    public function parse(string $text): string
    {
        return preg_replace_callback("#{(.*?)}#", function ($matches) {
            $firstMatch = trim($matches[1]);

            $lookUp  = $firstMatch;
            $default = null;

            if (str_contains($firstMatch, ',fallback=')) {
                [$lookUp, $default] = explode(',fallback=', $firstMatch);
            }

            if (isset($this->replacements[$lookUp]) && $this->replacements[$lookUp] !== null) {
                return $this->replacements[$lookUp];
            }

            return $default ?? '';
        }, $text);
    }
}
