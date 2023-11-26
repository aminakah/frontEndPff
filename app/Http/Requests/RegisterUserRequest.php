<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUserRequest extends FormRequest
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
                'prenom' => 'required|string|max:50',
                'nom' => 'required|string|max:50',
                'adresse' => 'required|string',
                'telephone' => 'required|numeric|min:9',
                'email' => 'required|email|unique:users|max:50',
                'password' => 'required|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ];
        }else{
            return [
                'prenom' => 'required|string|max:50',
                'nom' => 'required|string|max:50',
                'adresse' => 'required|string',
                'telephone' => 'required|numeric|min:9',
                'email' => 'required|email|unique:users|max:50',
                'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ];
        }
        
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
        return [
            'prenom.required' => 'Le prénom est requis.',
            'nom.required' => 'Le nom est requis.',
            'adresse.required' => 'L\'adresse est requis.',
            'telephone.min' => 'Le numéro de téléphone doit avoir au moins :min chiffres.',
            'email.unique' => 'Cette adresse email existe deja',
            'email.required' => 'Un adresse email doit etre fourni',
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit avoir au moins :min caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial (@$!%*?&).',
        ];
    }
}
