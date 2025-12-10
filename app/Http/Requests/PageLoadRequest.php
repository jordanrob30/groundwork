<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for page load metrics submission.
 */
class PageLoadRequest extends FormRequest
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
            'page' => ['required', 'string', 'max:255'],
            'load_duration' => ['required', 'numeric', 'min:0'],
            'dom_ready_duration' => ['nullable', 'numeric', 'min:0'],
            'device_type' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
        ];
    }
}
