<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for JavaScript error submission.
 */
class JsErrorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000'],
            'error_type' => ['nullable', 'string', 'max:100'],
            'page' => ['required', 'string', 'max:255'],
            'stack' => ['nullable', 'string', 'max:5000'],
            'user_agent' => ['nullable', 'string', 'max:500'],
        ];
    }
}
