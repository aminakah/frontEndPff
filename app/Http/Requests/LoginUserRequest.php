<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginUserRequest extends FormRequest
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
        return [
            //
            
            'email' => 'required|email|exists:users,email',
            'password' => 'required'

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'succes'=> false,
            'status_code' => 422,
            'error' => true,
            'message' => 'Erreur de validation',
            'errorsList' => $validator->errors()
        ]));
        
    }

    public function messages()
    {
        return[ 
            'email.required' => 'Email non fourni',
            'email.email' => 'Adresse email non valide',
            'name.exists' => 'Cette adresse mail n\'existe deja',
            'password.required' => 'Mot de pas non fourni'

        ];
    }
}
