<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'make' => ['required', 'string','max:64'],
            'model' => ['required', 'string','max:64'],
            'year' => [
                'required',
                'integer',
                'numeric',
                'between:1900,' . now()->year,
            ],
        ];
    }
}
