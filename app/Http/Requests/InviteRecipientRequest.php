<?php

namespace App\Http\Requests;

use App\Models\Calendar;
use Illuminate\Foundation\Http\FormRequest;

class InviteRecipientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $calendar = $this->route('calendar');

        if (!$calendar instanceof Calendar) {
            return false;
        }

        $user = $this->user();

        // Admins can always invite recipients
        if ($user?->is_admin) {
            return true;
        }

        // Only owners can invite recipients
        return $user && $user->id === $calendar->owner_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
