<?php

declare(strict_types=1);

namespace ParityPress\Framework\Http;

use WP_REST_Request;
use ParityPress\Validation\Validator;

abstract class Controller
{
    public function middleware()
    {
        return true;
    }

    public function validate(WP_REST_Request $request, array $rules, array $messages = [])
    {
        $validator = new Validator();

        return $validator->validate($request->get_params(), $rules, $messages);
    }
}
