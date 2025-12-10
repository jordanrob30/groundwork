<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for Web Vitals metrics submission.
 */
class WebVitalsRequest extends FormRequest
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
            'name' => ['required', 'string', 'in:LCP,FID,CLS,INP,TTFB'],
            'value' => ['required', 'numeric', 'min:0'],
            'page' => ['required', 'string', 'max:255'],
            'device_type' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
        ];
    }
}
