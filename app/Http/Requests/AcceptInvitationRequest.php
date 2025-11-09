<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AcceptInvitationRequest extends FormRequest
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
        return [
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $invitation = \App\Models\Invitation::where('token', $this->token)->first();

            if (!$invitation) {
                throw ValidationException::withMessages([
                    'token' => ['Deze uitnodiging is ongeldig.'],
                ]);
            }

            if ($invitation->isExpired()) {
                throw ValidationException::withMessages([
                    'token' => ['Deze uitnodiging is verlopen.'],
                ]);
            }

            if ($invitation->isAccepted()) {
                throw ValidationException::withMessages([
                    'token' => ['Deze uitnodiging is al gebruikt.'],
                ]);
            }
        });
    }
}
