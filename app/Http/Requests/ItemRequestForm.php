<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * التحقق من نموذج إضافة / تعديل غرض
 */
class ItemRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // عند الإضافة لازم صورة وحدة على الأقل، وعند التعديل الصور اختيارية
        $isCreate = $this->isMethod('post');

        return [
            // الخطوة 1: معلومات الغرض والصور
            'images' => [$isCreate ? 'required' : 'nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'category_id' => ['nullable', 'required_without:suggested_category', Rule::exists('categories', 'id')->where('state', 'active')],
            'suggested_category' => ['nullable', 'required_without:category_id', 'string', 'min:2', 'max:60'],
            'condition_id' => ['required', Rule::exists('conditions', 'id')],
            'count' => ['required', 'integer', 'min:1', 'max:50'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],

            // الخطوة 2: نوع العرض والسعر
            'transaction_type' => ['required', Rule::in(['sale', 'exchange', 'rent'])],
            'price' => ['nullable', 'required_unless:transaction_type,exchange', 'numeric', 'min:1', 'max:1000000000'],
            'exchange_for' => ['nullable', 'string', 'max:150'],
            'max_rent_duration' => ['nullable', 'required_if:transaction_type,rent', 'integer', 'min:1', 'max:365'],
            'rent_unit' => ['nullable', 'required_if:transaction_type,rent', Rule::in(['day', 'month', 'year'])],

            // الخطوة 3: الموقع
            'location_mode' => ['required', Rule::in(['account', 'custom'])],
            'location_hint' => ['required', 'string', 'max:120'],
            'latitude' => ['nullable', 'required_if:location_mode,custom', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_if:location_mode,custom', 'numeric', 'between:-180,180'],
        ];
    }

    // تحقق إضافي: مجموع الصور (القديمة + الجديدة) ما بيزيد عن 5
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // اذا اختار "عنوان حسابي" وحسابه ما فيه موقع، لازم يحدد على الخريطة
                if ($this->input('location_mode') === 'account' && ! $this->user()->hasLocation()) {
                    $validator->errors()->add('latitude', 'حسابك لا يحتوي على موقع، حدد موقع الغرض على الخريطة');
                }

                $item = $this->route('item');

                if (! $item) {
                    return;
                }

                $kept = $item->images()->whereNotIn('id', $this->input('remove_images', []))->count();
                $total = $kept + count($this->file('images', []));

                if ($total > 5) {
                    $validator->errors()->add('images', 'يمكنك رفع 5 صور كحد أقصى');
                }
                if ($total === 0) {
                    $validator->errors()->add('images', 'أضف صورة واحدة على الأقل');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'أضف صورة واحدة على الأقل',
            'images.max' => 'يمكنك رفع 5 صور كحد أقصى',
            'images.*.image' => 'الملف يجب أن يكون صورة',
            'images.*.mimes' => 'الصور المسموحة: JPG, PNG, WEBP',
            'images.*.max' => 'حجم الصورة 10 ميغابايت كحد أقصى',
            'title.*' => 'أدخل عنواناً واضحاً للإعلان (3 أحرف على الأقل)',
            'category_id.required_without' => 'اختر التصنيف أو اقترح تصنيفاً جديداً',
            'category_id.exists' => 'التصنيف المختار غير متاح',
            'suggested_category.required_without' => 'اختر التصنيف أو اقترح تصنيفاً جديداً',
            'suggested_category.*' => 'اسم التصنيف المقترح بين 2 و60 حرفاً',
            'condition_id.*' => 'اختر حالة الغرض',
            'count.*' => 'الكمية رقم من 1 إلى 50',
            'description.*' => 'اكتب وصفاً للغرض (10 أحرف على الأقل)',
            'transaction_type.*' => 'اختر نوع العرض',
            'price.*' => 'أدخل سعراً صحيحاً',
            'exchange_for.*' => 'وصف المقابل طويل جداً',
            'max_rent_duration.*' => 'أدخل أقصى مدة للتأجير',
            'rent_unit.*' => 'اختر وحدة التأجير',
            'location_mode.*' => 'اختر موقع الغرض',
            'location_hint.*' => 'أدخل اسم المنطقة',
            'latitude.*' => 'حدد الموقع على الخريطة',
            'longitude.*' => 'حدد الموقع على الخريطة',
        ];
    }
}
