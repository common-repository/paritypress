<?php

declare(strict_types=1);

namespace ParityPress\Framework\Http\Requests;

use WP_REST_Request;
use ParityPress\Framework\Validation\Validator;

abstract class FormRequest
{
    protected $request;
    protected $validator;

    public function __construct(WP_REST_Request $request)
    {
        $this->request = $request;
        $this->validator = new Validator();
    }

    abstract public function rules(): array;

    public function messages(): array
    {
        return [];
    }

    public function validate(): bool
    {
        $data = $this->getRequestData();
        $rules = $this->rules();
        $messages = $this->messages();

        return $this->validator->make($data, $rules, $messages)->validate();
    }

    public function validated(): array
    {
        return $this->validator->validated();
    }

    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }

    protected function getRequestData(): array
    {
        return $this->request->get_params();
    }
}
