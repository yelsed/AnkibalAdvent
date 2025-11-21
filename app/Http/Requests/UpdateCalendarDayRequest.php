<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarDayRequest extends FormRequest
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
            'gift_type' => ['required', 'in:text,image_text,product'],
            'title' => ['nullable', 'string', 'max:255'],
            'content_text' => ['required_if:gift_type,text', 'required_if:gift_type,image_text', 'nullable', 'string'],
            'content_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'product_code' => ['required_if:gift_type,product', 'nullable', 'string', 'max:255'],
            'audio_url' => ['nullable', 'url', 'max:2048'],
            'audio_file_id' => ['nullable', 'exists:audio_files,id'],
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
            'gift_type.required' => 'Please select a gift type.',
            'gift_type.in' => 'The selected gift type is invalid.',
            'content_text.required_if' => 'Content text is required for this gift type.',
            'content_image.image' => 'The file must be an image.',
            'content_image.mimes' => 'The image must be a file of type: jpeg, jpg, png, gif, or webp.',
            'content_image.max' => 'The image size cannot exceed 5MB.',
            'product_code.required_if' => 'Product code is required for product/coupon type.',
        ];
    }
}
