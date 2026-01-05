<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNormalReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:10', Rule::exists('session', 'session_id')],
            'contract_id' => ['required', 'string', 'max:10', Rule::exists('contract_info', 'contract_id')],
        ];
    }

    public function messages(): array
    {
        return [
            'session_id.required' => '枠を選択してください。',
            'session_id.exists' => '選択した枠が見つかりません。',
            'contract_id.required' => '利用する契約を選択してください。',
            'contract_id.exists' => '選択した契約が見つかりません。',
        ];
    }
}
