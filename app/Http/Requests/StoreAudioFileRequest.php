<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:10240'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select an audio file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be an audio file (MP3, WAV, OGG, M4A, or AAC).',
            'file.max' => 'The audio file size cannot exceed 10MB.',
            'name.required' => 'Please enter a name for this audio file.',
        ];
    }
}
