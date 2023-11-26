<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ModifierCategorieRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        if(request()->isMethod('post')) {
            return [
                'nom' => 'required|string',
                'description' => 'required|string',
            ];
        }else {
            # code...
            return [
                'nom' => 'required|string',
                'description' => 'required|string',
            ];
        }
    }
    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'succes'=> false,
            'error' => true,
            'message' => 'Erreur de validation',
            'errorsList' => $validator->errors()
        ]));
    }

    public function messages(){

        return [
            'nom.required' => 'Le nom est requis.',
            'description.required' => 'description du categorie est requis.',
            
        ];
    }
}
