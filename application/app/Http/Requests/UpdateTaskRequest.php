<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['string', 'max:255'],
            'description' => ['string'],
            'due_date' => ['date', 'after:now'],
            'caseNumber' => ['required', 'string'],
            'status' => ['required', 'string'],
        ];
    }
}