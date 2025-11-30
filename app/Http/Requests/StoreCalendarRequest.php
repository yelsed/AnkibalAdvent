<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'theme_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'audio_url' => ['nullable', 'url', 'max:2048'],
            'theme_type' => ['required', 'in:single,dual,seasonal'],
            'secondary_color' => ['nullable', 'required_if:theme_type,dual', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'seasonal_config' => ['nullable', 'required_if:theme_type,seasonal', 'array'],
        ];

        // Admins can specify user_id OR email (for new users)
        if ($this->user()?->is_admin) {
            $rules['user_id'] = ['nullable', 'required_without:email', 'exists:users,id'];
            $rules['email'] = ['nullable', 'required_without:user_id', 'email', 'max:255'];
            $rules['name'] = ['nullable', 'required_with:email', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set user_id to current user if not provided
        // For non-admins: always set to current user
        // For admins: only set if neither user_id nor email is provided (default to self)
        if (!$this->user()?->is_admin) {
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        } elseif (!$this->has('user_id') && !$this->has('email')) {
            // Admin creating calendar for themselves via /calendars route
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }

        // Decode seasonal_config if it's a JSON string
        if ($this->has('seasonal_config') && is_string($this->seasonal_config)) {
            $decoded = json_decode($this->seasonal_config, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'seasonal_config' => $decoded,
                ]);
            }
        }
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your advent calendar.',
            'year.required' => 'Please specify the year for the advent calendar.',
            'year.min' => 'The year must be 2000 or later.',
            'year.max' => 'The year cannot be later than 2100.',
            'theme_color.regex' => 'The theme color must be a valid hex color code.',
        ];
    }
}
