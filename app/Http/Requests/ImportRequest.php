<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * @property UploadedFile $file
 */
class ImportRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}
