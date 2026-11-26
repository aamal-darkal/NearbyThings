{{-- صفحة إضافة غرض جديد / تعديل غرض (نفس النموذج، 3 خطوات) --}}
@extends('layouts.site')

@php
  $isEdit = $item->exists;

  // أي خطوة فيها خطأ من السيرفر؟ منفتح عليها مباشرة
  $startStep = 1;
  if ($errors->hasAny(['transaction_type', 'price', 'exchange_for', 'max_rent_duration', 'rent_unit'])) {
      $startStep = 2;
  }
  if ($errors->hasAny(['location_mode', 'location_hint', 'latitude', 'longitude'])) {
      $startStep = 3;
  }
  if ($errors->hasAny(['images', 'images.*', 'title', 'category_id', 'suggested_category', 'condition_id', 'count', 'description'])) {
      $startStep = 1;
  }

  $type = old('transaction_type', $item->transaction_type);
  $existingImages = $isEdit ? $item->images : collect();
  $hasCustomLocation = $isEdit && ($item->latitude !== $user->latitude || $item->longitude !== $user->longitude);
  $locationMode = old('location_mode', ($hasCustomLocation || ! $user->hasLocation()) ? 'custom' : 'account');
@endphp

@section('title', $isEdit ? 'تعديل الغرض' : 'إضافة غرض جديد')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="{{ asset('assets/css/item-form.css') }}">
@endpush

@section('content')
  <main class="page">
    <div class="container">
      <!-- عنوان الصفحة -->
      <div class="form-hero">
        <span class="form-hero__eyebrow"><x-icon name="plus-circle" />نشر إعلان مجاني بين الجيران</span>
        <h1>{{ $isEdit ? 'تعديل الغرض' : 'إضافة غرض جديد' }}</h1>
        <p>شارك غرضك مع جيرانك للبيع أو التبادل أو التأجير بكل أمان وسهولة</p>
      </div>

      <!-- مؤشر الخطوات -->
      <ol class="stepper" aria-label="خطوات النشر">
        <li class="stepper__step" data-step="1"><span class="stepper__num">1</span><span>معلومات الغرض والصور</span></li>
        <li class="stepper__step" data-step="2"><span class="stepper__num">2</span><span>نوع العرض والسعر</span></li>
        <li class="stepper__step" data-step="3"><span class="stepper__num">3</span><span>الموقع ونشر الإعلان</span></li>
      </ol>

      <div class="form-layout">
        <form class="panel form-card" id="item-form" method="POST" enctype="multipart/form-data" novalidate
              action="{{ $isEdit ? route('items.update', $item) : route('items.store') }}"
              data-start-step="{{ $startStep }}">
          @csrf
          @if ($isEdit)
            @method('PUT')
          @endif

          {{-- ===== الخطوة 1: معلومات الغرض والصور ===== --}}
          <section class="form-step" data-step="1">
            <div class="form-step__head">
              <span class="form-step__num">1</span>
              <div>
                <h2>معلومات الغرض والصور</h2>
                <p>الصور الجيدة والوصف الواضح يساعدان جيرانك في التواصل السريع</p>
              </div>
              <span class="tag">الخطوة الأولى</span>
            </div>

            <div class="form-group @error('images') has-error @enderror @error('images.*') has-error @enderror">
              <div class="form-label-row">
                <span class="form-label">صور الغرض (حتى 5 صور) <span class="required">*</span></span>
                <span class="muted" id="photos-count">تم تحميل {{ $existingImages->count() }} من 5</span>
              </div>

              <button type="button" class="dropzone" id="dropzone">
                <span class="dropzone__icon"><x-icon name="camera" /></span>
                <strong>اسحب الصور وأفلتها هنا أو تصفح ملفاتك</strong>
                <span>يدعم صيغ JPG، PNG، WEBP بحد أقصى 10 ميغابايت لكل صورة</span>
                <span class="dropzone__note">ملاحظة: أول صورة ستكون الصورة الرئيسية للإعلان</span>
              </button>

              <div class="photo-slots">
                {{-- الصور المحفوظة (عند التعديل): مربع اختيار لحذفها --}}
                @foreach ($existingImages as $image)
                  <div class="photo-slot has-image" data-existing="true">
                    <img src="{{ $image->url() }}" alt="صورة الغرض {{ $loop->iteration }}">
                    @if ($loop->first)<span class="photo-slot__main">الرئيسية</span>@endif
                    <label class="photo-slot__remove" title="حذف الصورة">
                      <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" class="js-remove-existing">
                      <x-icon name="trash" />
                      <span class="sr-only">حذف الصورة</span>
                    </label>
                  </div>
                @endforeach

                {{-- مربعات لرفع صور جديدة (كل مربع صورة وحدة) --}}
                @for ($i = $existingImages->count() + 1; $i <= 5; $i++)
                  <div class="photo-slot">
                    <label class="photo-slot__picker">
                      <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" class="js-photo-input">
                      <img alt="" hidden>
                      <span class="photo-slot__empty"><x-icon name="plus" />صورة {{ $i }}</span>
                    </label>
                    <button type="button" class="photo-slot__remove js-clear-photo" aria-label="إزالة الصورة" hidden><x-icon name="trash" /></button>
                  </div>
                @endfor
              </div>
              <p class="form-error">{{ $errors->first('images') ?: $errors->first('images.*') }}</p>
            </div>

            <div class="form-group @error('title') has-error @enderror">
              <label class="form-label" for="item-title">عنوان الإعلان <span class="required">*</span></label>
              <input class="input" type="text" id="item-title" name="title" maxlength="120" required
                     value="{{ old('title', $item->title) }}" placeholder="مثال: دراجة هوائية جبلية مقاس 26">
              <p class="form-error">@error('title'){{ $message }}@enderror</p>
              <p class="form-hint">العنوان المختصر والمميز يسهل عثور الجيران عليه</p>
            </div>

            <div class="form-group @error('category_id') has-error @enderror @error('suggested_category') has-error @enderror">
              <div class="form-label-row">
                <label class="form-label" for="item-category">التصنيف <span class="required">*</span></label>
                <button type="button" class="form-hint-link" id="suggest-toggle">اقترح تصنيفاً جديداً</button>
              </div>
              <select class="select" id="item-category" name="category_id" @if (old('suggested_category')) disabled @endif>
                <option value="">اختر التصنيف</option>
                @foreach ($categories as $category)
                  <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->category_name }}</option>
                @endforeach
              </select>
              <div class="suggest-box" id="suggest-box" @if (! old('suggested_category')) hidden @endif>
                <label class="form-label" for="item-suggest">اسم التصنيف المقترح</label>
                <input class="input" type="text" id="item-suggest" name="suggested_category" maxlength="60"
                       value="{{ old('suggested_category') }}" placeholder="مثال: معدات تصوير" @if (! old('suggested_category')) disabled @endif>
                <p class="form-hint">يُنشر إعلانك فوراً، ويظهر في فلاتر التصنيفات بعد موافقة المشرف على التصنيف.</p>
              </div>
              <p class="form-error">{{ $errors->first('category_id') ?: $errors->first('suggested_category') }}</p>
            </div>

            <fieldset class="form-group @error('condition_id') has-error @enderror">
              <legend class="form-label">حالة الغرض <span class="required">*</span></legend>
              <div class="choice-chips">
                @foreach ($conditions as $condition)
                  <label class="choice-chip">
                    <input type="radio" name="condition_id" value="{{ $condition->id }}" @checked(old('condition_id', $item->condition_id) == $condition->id)>
                    <span>{{ $condition->condition_name }}</span>
                  </label>
                @endforeach
              </div>
              <p class="form-error">@error('condition_id'){{ $message }}@enderror</p>
            </fieldset>

            <div class="form-group form-group--small @error('count') has-error @enderror">
              <label class="form-label" for="item-count">الكمية</label>
              <input class="input" type="number" id="item-count" name="count" min="1" max="50" value="{{ old('count', $item->count) }}">
              <p class="form-error">@error('count'){{ $message }}@enderror</p>
            </div>

            <div class="form-group @error('description') has-error @enderror">
              <label class="form-label" for="item-description">وصف الغرض <span class="required">*</span></label>
              <textarea class="textarea" id="item-description" name="description" maxlength="1000" rows="5"
                        placeholder="اذكر الحالة والمواصفات وسبب العرض">{{ old('description', $item->description) }}</textarea>
              <div class="form-hint-row">
                <span class="form-hint">كن دقيقاً وصادقاً لبناء تقييم موثوق في مجتمعك المحلي</span>
                <span class="form-hint" id="description-count">0 حرف</span>
              </div>
              <p class="form-error">@error('description'){{ $message }}@enderror</p>
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn--primary js-next">التالي: نوع العرض والسعر <x-icon name="arrow-left" /></button>
              <a href="{{ route('items.mine') }}" class="btn btn--soft">إلغاء</a>
            </div>
          </section>

          {{-- ===== الخطوة 2: نوع العرض والسعر ===== --}}
          <section class="form-step" data-step="2" hidden>
            <div class="form-step__head">
              <span class="form-step__num">2</span>
              <div>
                <h2>نوع العرض والتسعير</h2>
                <p>اختر أسلوب المعاملة المفضل لديك مع جيرانك</p>
              </div>
              <span class="tag">الخطوة الثانية</span>
            </div>

            <fieldset class="type-cards">
              <legend class="sr-only">نوع العرض</legend>
              <label class="type-card">
                <input type="radio" name="transaction_type" value="sale" @checked($type === 'sale')>
                <span class="type-card__icon"><x-icon name="tag" /></span>
                <strong>بيع</strong>
                <span>بيع الغرض نقداً لجيرانك في المنطقة بسعر محدد</span>
              </label>
              <label class="type-card">
                <input type="radio" name="transaction_type" value="exchange" @checked($type === 'exchange')>
                <span class="type-card__icon"><x-icon name="repeat" /></span>
                <strong>تبادل</strong>
                <span>استبدال الغرض بغرض آخر يناسب احتياجاتك الحالية</span>
              </label>
              <label class="type-card">
                <input type="radio" name="transaction_type" value="rent" @checked($type === 'rent')>
                <span class="type-card__icon"><x-icon name="calendar" /></span>
                <strong>تأجير</strong>
                <span>تأجير الغرض لفترة محددة بسعر يومي أو شهري</span>
              </label>
            </fieldset>

            <div class="price-panel">
              {{-- السعر: للبيع والتأجير --}}
              <div class="form-group js-for-type @error('price') has-error @enderror" data-types="sale rent">
                <label class="form-label" for="item-price">
                  <span class="js-price-label">{{ $type === 'rent' ? 'سعر التأجير (ل.س)' : 'السعر المطلوب (ل.س)' }}</span> <span class="required">*</span>
                </label>
                <div class="input-wrap input-wrap--light">
                  <input type="number" id="item-price" name="price" min="1" value="{{ old('price', $item->price ?: '') }}" placeholder="مثال: 4500">
                  <span class="input-suffix">ليرة سورية</span>
                </div>
                <p class="form-error">@error('price'){{ $message }}@enderror</p>
              </div>

              {{-- مدة التأجير: للتأجير فقط --}}
              <div class="form-grid-2 js-for-type" data-types="rent">
                <div class="form-group @error('rent_unit') has-error @enderror">
                  <label class="form-label" for="item-rent-unit">وحدة التأجير <span class="required">*</span></label>
                  <select class="select" id="item-rent-unit" name="rent_unit">
                    <option value="day" @selected(old('rent_unit', $item->rent_unit) === 'day')>يوم</option>
                    <option value="month" @selected(old('rent_unit', $item->rent_unit) === 'month')>شهر</option>
                    <option value="year" @selected(old('rent_unit', $item->rent_unit) === 'year')>سنة</option>
                  </select>
                  <p class="form-error">@error('rent_unit'){{ $message }}@enderror</p>
                </div>
                <div class="form-group @error('max_rent_duration') has-error @enderror">
                  <label class="form-label" for="item-max-rent">أقصى مدة متاحة للتأجير <span class="required">*</span></label>
                  <input class="input" type="number" id="item-max-rent" name="max_rent_duration" min="1" max="365"
                         value="{{ old('max_rent_duration', $item->max_rent_duration) }}" placeholder="مثال: 7">
                  <p class="form-error">@error('max_rent_duration'){{ $message }}@enderror</p>
                </div>
              </div>

              {{-- المقابل المطلوب: للتبادل فقط --}}
              <div class="form-group js-for-type @error('exchange_for') has-error @enderror" data-types="exchange">
                <label class="form-label" for="item-exchange-for">ماذا تريد مقابله؟ (اختياري)</label>
                <input class="input" type="text" id="item-exchange-for" name="exchange_for" maxlength="150"
                       value="{{ old('exchange_for', $item->variants['exchange_for'] ?? '') }}" placeholder="مثال: أداة بستنة أو خيمة">
                <p class="form-error">@error('exchange_for'){{ $message }}@enderror</p>
                <p class="form-hint">في التبادل، اذكر بدائل متعددة لفتح المجال لخيارات أوسع من جيرانك</p>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn--primary js-next">التالي: الموقع ونشر الإعلان <x-icon name="arrow-left" /></button>
              <button type="button" class="btn btn--soft js-prev">السابق</button>
            </div>
          </section>

          {{-- ===== الخطوة 3: الموقع والنشر ===== --}}
          <section class="form-step" data-step="3" hidden>
            <div class="form-step__head">
              <span class="form-step__num">3</span>
              <div>
                <h2>الموقع والمراجعة النهائية</h2>
                <p>تأكيد النطاق الجغرافي لظهور الإعلان لجيرانك</p>
              </div>
              <span class="tag">الخطوة الثالثة</span>
            </div>

            <fieldset class="form-group">
              <legend class="form-label">نطاق تواجد الغرض</legend>
              <div class="location-cards">
                <label class="location-card @if (! $user->hasLocation()) is-disabled @endif">
                  <input type="radio" name="location_mode" value="account" @checked($locationMode === 'account') @disabled(! $user->hasLocation())>
                  <x-icon name="map-pin" />
                  <span>
                    <strong>عنوان حسابي الأساسي</strong>
                    <small>{{ $user->hasLocation() ? $user->areaLabel() : 'لم تحدد موقعك في الملف الشخصي' }}</small>
                  </span>
                </label>
                <label class="location-card">
                  <input type="radio" name="location_mode" value="custom" @checked($locationMode === 'custom')>
                  <x-icon name="crosshair" />
                  <span>
                    <strong>تحديد موقع آخر على الخريطة</strong>
                    <small>مقر العمل أو حي بديل</small>
                  </span>
                </label>
              </div>
            </fieldset>

            <div class="form-group @error('location_hint') has-error @enderror">
              <label class="form-label" for="item-area">اسم المنطقة (يظهر للجيران) <span class="required">*</span></label>
              <input class="input" type="text" id="item-area" name="location_hint" maxlength="120"
                     value="{{ old('location_hint', $item->location_hint ?: $user->address) }}" placeholder="مثال: المزة - قرب حديقة الطلائع">
              <p class="form-error">@error('location_hint'){{ $message }}@enderror</p>
            </div>

            <div class="form-group js-custom-location @error('latitude') has-error @enderror" @if ($locationMode !== 'custom') hidden @endif>
              <div class="form-label-row">
                <span class="form-label">اضغط على الخريطة لتحديد المنطقة</span>
                <button type="button" class="form-hint-link js-locate-me">استخدم موقعي الحالي</button>
              </div>
              <div class="map-box js-location-picker" data-lat-input="item-lat" data-lng-input="item-lng"></div>
              <input type="hidden" id="item-lat" name="latitude" value="{{ old('latitude', $hasCustomLocation ? $item->latitude : '') }}">
              <input type="hidden" id="item-lng" name="longitude" value="{{ old('longitude', $hasCustomLocation ? $item->longitude : '') }}">
              <p class="form-error">@error('latitude'){{ $message }}@enderror</p>
            </div>

            <div class="alert">
              <x-icon name="lock" />
              <div><strong>حماية الخصوصية:</strong> يظهر موقعك للجيران بشكل تقريبي فقط، ولا تظهر بيانات تواصلك إلا بعد قبولك للطلب.</div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn--primary btn--lg"><x-icon name="upload" />{{ $isEdit ? 'حفظ التعديلات' : 'نشر الإعلان الآن' }}</button>
              <button type="button" class="btn btn--soft js-prev">السابق</button>
            </div>
          </section>
        </form>

        <!-- العمود الجانبي: معاينة البطاقة ونصائح -->
        <aside class="form-aside">
          <div class="panel preview-card">
            <div class="preview-card__head">
              <h2>معاينة بطاقة الإعلان</h2>
              <span class="tag tag--accent"><x-icon name="eye" />كما تظهر للجيران</span>
            </div>
            <article class="item-card">
              <div class="item-card__media">
                <img id="preview-image" src="{{ $existingImages->first()?->url() }}" alt="" @if ($existingImages->isEmpty()) hidden @endif>
                <span class="item-card__placeholder" id="preview-placeholder" @if ($existingImages->isNotEmpty()) hidden @endif><x-icon name="image" /></span>
                <span class="badge badge--{{ $type }}" id="preview-badge">{{ ['sale' => 'بيع', 'exchange' => 'تبادل', 'rent' => 'تأجير'][$type] }}</span>
              </div>
              <div class="item-card__body">
                <span class="item-card__distance" id="preview-category">{{ $item->category?->category_name ?? 'التصنيف' }}</span>
                <h3 class="item-card__title" id="preview-title">{{ $item->title ?: 'عنوان الإعلان' }}</h3>
                <div class="item-card__footer">
                  <span class="item-card__price" id="preview-price">{{ $item->exists ? $item->priceLabel() : '—' }}</span>
                  <span class="item-card__area" id="preview-area">{{ $item->location_hint ?: $user->address }}</span>
                </div>
              </div>
            </article>
            <p class="muted preview-card__note">تحديث مباشر مع كل خطوة تقوم بإكمالها في النموذج.</p>
          </div>

          <div class="tips">
            <h2><x-icon name="lightbulb" />نصائح لنشر ناجح وسريع</h2>
            <ul>
              <li><x-icon name="check-circle" />التقط صوراً في إضاءة نهارية طبيعية تُبرز حالة الغرض بصدق.</li>
              <li><x-icon name="check-circle" />الأسعار المناسبة للحي ترفع سرعة التواصل إلى 3 أضعاف.</li>
              <li><x-icon name="check-circle" />في التبادل، اذكر بدائل متعددة لفتح المجال لخيارات واسعة من جيرانك.</li>
            </ul>
          </div>
        </aside>
      </div>
    </div>
  </main>
@endsection

@push('libraries')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/location-picker.js') }}"></script>
  <script src="{{ asset('assets/js/item-form.js') }}"></script>
@endpush
