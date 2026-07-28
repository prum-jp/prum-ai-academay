<?php

namespace App\Http\Requests;

use App\Services\StudentAvatarService;
use Illuminate\Foundation\Http\FormRequest;

class UploadStudentAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:'.StudentAvatarService::MAX_KILOBYTES,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.required' => '画像を選択してください。',
            'avatar.image' => '画像ファイルを選択してください。',
            'avatar.mimetypes' => 'jpeg / png / webp 形式の画像を選択してください。',
            'avatar.max' => '画像サイズは 5MB 以下にしてください。',
        ];
    }
}
