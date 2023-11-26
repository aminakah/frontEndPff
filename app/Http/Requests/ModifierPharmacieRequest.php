<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ModifierPharmacieRequest extends FormRequest
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
        $rules = [
            'nom' => 'required|string',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'adresse' => 'required|string',
            'telephone' => 'required|numeric|min:9',
            'fax' => 'required|numeric|min:9',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['photo'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'photo.image' => 'Le fichier doit être une image (jpeg, png, jpg, gif).',
            'photo.mimes' => 'Les formats d\'image autorisés sont : jpeg, png, jpg, gif.',
            'photo.max' => 'La taille maximale de l\'image est de 2048 kilo-octets.',
            'adresse.required' => 'L\'adresse est requise.',
            'telephone.min' => 'Le numéro de téléphone doit avoir au moins :min chiffres.',
            'fax.required' => 'Le fax doit être fourni.',
        ];
    }
    public function failedValidation(Validator $validator){
        throw new HttpResponseException(response()->json([
            'succes'=> false,
            'error' => true,
            'message' => 'Erreur de validation',
            'errorsList' => $validator->errors()
        ]));
    }


}
