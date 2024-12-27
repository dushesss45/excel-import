<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcelUploadRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Excel-файл обязателен к загрузке!',
            'file.mimes' => 'Допустим только формат .xlsx!',
        ];
    }
}
