<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarLibrosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'biblioteca_id' => ['required', 'integer', 'exists:bibliotecas,id'],
            'tipo_registro_id' => ['required', 'integer', 'exists:tipo_registros,id'],
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ];
    }
}
