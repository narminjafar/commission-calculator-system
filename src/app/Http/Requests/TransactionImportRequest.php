<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv' => 'required|file|mimes:csv,txt',
            'user_type' => 'nullable|string',
            'operation_type' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'csv.required' => 'CSV faylı mütləq əlavə olunmalıdır.',
            'csv.mimes' => 'Fayl yalnız CSV və ya TXT formatında ola bilər.',
        ];
    }
}
