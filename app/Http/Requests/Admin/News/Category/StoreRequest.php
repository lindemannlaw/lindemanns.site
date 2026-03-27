<?php

namespace App\Http\Requests\Admin\News\Category;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules['sort'] = ['required', 'integer'];
        $rules['active'] = ['required', 'boolean'];

        foreach (supported_languages_keys() as $locale) {
            $rules['name'] = ['required', 'array'];
            $rules['name.' . $locale] = ['required', 'string', 'max:255'];

            //$rules['seo_title'] = ['nullable', 'array'];
            //$rules['seo_title.' . $locale] = ['nullable', 'string', 'max:255'];
            //$rules['seo_description'] = ['nullable', 'array'];
            //$rules['seo_description.' . $locale] = ['nullable', 'string', 'max:255'];
            //$rules['seo_keywords'] = ['nullable', 'array'];
            //$rules['seo_keywords.' . $locale] = ['nullable', 'string', 'max:255'];
            //$rules['geo_text'] = ['nullable', 'array'];
            //$rules['geo_text.' . $locale] = ['nullable', 'string', 'max:5000'];
        }

        return $rules;
    }
}
