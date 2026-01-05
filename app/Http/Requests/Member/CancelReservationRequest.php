<?php

namespace App\Http\Requests\Member;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $member = $this->user();
        $reservation = $this->route('reservation');

        return $member !== null
            && $reservation instanceof Reservation
            && (string) $reservation->member_id === (string) $member->getKey();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cancel_reason' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.max' => 'キャンセル理由は200文字以内で入力してください。',
        ];
    }
}
