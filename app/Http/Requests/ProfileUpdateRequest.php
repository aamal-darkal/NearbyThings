<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['required', 'regex:/^09[0-9]{8}$/'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            // الموقع التقريبي من الخريطة (اختياري)
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            // كلمة المرور اختيارية: اذا انكتبت كلمة جديدة لازم الحالية تكون صحيحة
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * رسائل الخطأ بالعربي
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.*' => 'أدخل اسمك الكامل',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم مسبقاً',
            'email.lowercase' => 'اكتب البريد الإلكتروني بأحرف صغيرة',
            'email.*' => 'أدخل بريداً إلكترونياً صحيحاً',
            'phone.*' => 'رقم الهاتف يجب أن يبدأ بـ 09 ويتكون من 10 أرقام',
            'address.*' => 'أدخل منطقتك',
            'city.*' => 'اسم المدينة طويل جداً',
            'latitude.*' => 'حدد موقعك على الخريطة بشكل صحيح',
            'longitude.*' => 'حدد موقعك على الخريطة بشكل صحيح',
            'current_password.required_with' => 'أدخل كلمة المرور الحالية',
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين',
            'password.*' => 'كلمة المرور 8 أحرف على الأقل',
        ];
    }
}
