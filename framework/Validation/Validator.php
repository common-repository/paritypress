<?php

declare(strict_types=1);

namespace ParityPress\Framework\Validation;

class Validator
{
    protected $data = [];
    protected $rules = [];
    protected $messages = [];
    protected $errors = [];
    protected $validatedData = [];

    public function make(array $data, array $rules, array $messages = []): Validator
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
        return $this;
    }

    public function validate(): bool
    {
        $this->errors = [];
        $this->validatedData = [];

        foreach ($this->rules as $field => $fieldRules) {
            $this->validateField($field, $fieldRules);
        }

        return empty($this->errors);
    }

    public function validated(): array
    {
        return $this->validatedData;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function validateField(string $field, $fieldRules): void
    {
        $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
        $value = $this->getFieldValue($field);

        // Handle wildcard fields like discounts.*.id
        if (strpos($field, '.*.') !== false) {
            $this->validateArrayItems($field, $fieldRules);
            return;
        }

        foreach ($fieldRules as $rule) {
            if ($rule instanceof \Closure) {
                if (!$rule($value, $this->data)) {
                    $this->addError($field, $this->getMessage($field, 'custom'));
                } else {
                    $this->setValidatedData($field, $value);
                }
                continue;
            }

            $ruleParts = explode(':', $rule);
            $ruleName = $ruleParts[0];
            $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];

            $methodName = 'validate' . ucfirst($ruleName);
            if (method_exists($this, $methodName)) {
                if (!$this->$methodName($field, $value, $ruleParams)) {
                    break;
                }

                $this->setValidatedData($field, $value);
            }
        }
    }

    protected function validateArrayItems(string $field, array $fieldRules): void
    {
        $fieldBase = substr($field, 0, strpos($field, '.*.'));
        $subfield = substr($field, strpos($field, '.*.') + 3);
        $arrayData = $this->getFieldValue($fieldBase);

        if (is_array($arrayData)) {
            foreach ($arrayData as $index => $item) {
                $this->validateField("{$fieldBase}.{$index}.{$subfield}", $fieldRules);
            }
        }
    }

    protected function getFieldValue(string $field)
    {
        $keys = explode('.', $field);
        $value = $this->data;
        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        return $value;
    }

    protected function setValidatedData(string $field, $value): void
    {
        $keys = explode('.', $field);
        $data = &$this->validatedData;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                $data[$key] = [];
            }
            $data = &$data[$key];
        }
        $data = $value;
    }

    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    protected function getMessage(string $field, string $rule): string
    {
        $key = "{$field}.{$rule}";
        return $this->messages[$key] ?? $this->getDefaultMessage($field, $rule);
    }

    protected function getDefaultMessage(string $field, string $rule): string
    {
        $messages = [
            // Translators: %s is the field name.
            'required' => sprintf(__('%s is required.', 'parity-press'), $field),

            // Translators: %s is the field name.
            'date' => sprintf(__('%s must be a valid date in the format YYYY-MM-DD.', 'parity-press'), $field),

            // Translators: %s is the field name.
            'hexColor' => sprintf(__('%s must be a valid hex color code.', 'parity-press'), $field),

            // Translators: %s is the field name.
            'integer' => sprintf(__('%s must be an integer.', 'parity-press'), $field),

            // Translators: %1$s is the field name, and %2$s is the minimum value.
            'min' => sprintf(__('%1$s must be at least %2$s.', 'parity-press'), $field, '{min}'),

            // Translators: %s is the field name.
            'array' => sprintf(__('%s must be an array.', 'parity-press'), $field),

            // Translators: %s is the field name.
            'custom' => sprintf(__('%s is invalid.', 'parity-press'), $field),
        ];

        // Translators: %s is the field name.
        return $messages[$rule] ?? sprintf(__('%s is invalid.', 'parity-press'), $field);
    }

    protected function validateRequired(string $field, $value): bool
    {
        if ($value === null || $value === '') {
            $this->addError($field, $this->getMessage($field, 'required'));
            return false;
        }
        return true;
    }

    protected function validateDate(string $field, $value): bool
    {
        if (!empty($value)) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->addError($field, $this->getMessage($field, 'date'));
                return false;
            }
        }
        return true;
    }

    protected function validateHexColor(string $field, $value): bool
    {
        if (!empty($value) && !preg_match('/^#[a-f0-9]{6}$/i', $value)) {
            $this->addError($field, $this->getMessage($field, 'hexColor'));
            return false;
        }
        return true;
    }

    protected function validateInteger(string $field, $value): bool
    {
        if ($value !== null && !is_int($value)) {
            $this->addError($field, $this->getMessage($field, 'integer'));
            return false;
        }
        return true;
    }

    protected function validateMin(string $field, $value, array $params): bool
    {
        $min = (int)$params[0];
        if ($value !== null && $value < $min) {
            $this->addError($field, str_replace('{min}', $min, $this->getMessage($field, 'min')));
            return false;
        }
        return true;
    }

    protected function validateArray(string $field, $value): bool
    {
        if ($value !== null && !is_array($value)) {
            $this->addError($field, $this->getMessage($field, 'array'));
            return false;
        }
        return true;
    }
}
