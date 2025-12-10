<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for Livewire component metrics submission.
 */
class LivewireMetricsRequest extends FormRequest
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
            'component' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'in:render,update'],
            'duration' => ['required', 'numeric', 'min:0'],
            'page' => ['nullable', 'string', 'max:255'],
        ];
    }
}
