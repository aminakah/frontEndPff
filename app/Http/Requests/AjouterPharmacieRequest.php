<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AjouterPharmacieRequest extends FormRequest
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
            //
            if(request()->isMethod('post')) {
                return [

                    'nom' => 'required|string',
                    'adresse' => 'required|string',
                    'telephone' => 'required|numeric|min:9',
                    'fax'  => 'required|numeric|min:9',
                    'photo' => 'required|max:10000', // Exemple de validation pour la photo
                    'quartier_id' => 'required|exists:quartiers,id'
                ];
            }else{
                return [

                    'nom' => 'required|string',
                    'adresse' => 'required|string',
                    'telephone' => 'required|numeric|min:9',
                    'fax'  => 'required|numeric|min:9',
                    'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Exemple de validation pour la photo
                    'quartier_id' => 'required|exists:quartiers,id'
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
            'adresse.required' => 'L\'adresse est requis.',
            'telephone.min' => 'Le numéro de téléphone doit avoir au moins :min chiffres.',
            'fax.required' => 'fax doit etre fourni',
            'photo.image' => 'La photo doit être une image.',
            'photo.mimes' => 'La photo doit être de type :jpeg, .png, .jpg ou .gif',
            'photo.max' => 'La taille maximale de la photo est 2 Mo.',
            'quartier_id.required' => 'L\'ID du quartier est requis.',
            'quartier_id.exists' => 'L\'ID du quartier est invalide.'
        ];
    }
}
