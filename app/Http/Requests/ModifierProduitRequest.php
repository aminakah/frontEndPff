<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class ModifierProduitRequest extends FormRequest
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
    public function rules()
    {
        return [
            'nom' => 'string',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'prix' => 'numeric|min:0',
            'quantite' => 'integer|min:0',
            'date_expiration' => 'nullable|date|after_or_equal:today',
            'categorie_id' => 'exists:categories,id',
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

    public function messages()
    {
        return [
            'photo.image' => 'Le fichier doit être une image (jpeg, png, jpg, gif).',
            'photo.mimes' => 'Les formats d\'image autorisés sont : jpeg, png, jpg, gif.',
            'photo.max' => 'La taille maximale de l\'image est de 2048 kilo-octets.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'prix.numeric' => 'Le prix doit être un nombre.',
            'prix.min' => 'Le prix ne peut pas être inférieur à zéro.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité ne peut pas être inférieure à zéro.',
            'date_expiration.date' => 'La date d\'expiration doit être une date valide.',
            'date_expiration.after_or_equal' => 'La date d\'expiration doit être ultérieure ou égale à la date actuelle.',
            'categorie_id.exists' => 'La catégorie sélectionnée est invalide.',
        ];
    }
}
