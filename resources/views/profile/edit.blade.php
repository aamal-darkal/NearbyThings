{{-- صفحة تعديل الملف الشخصي --}}
@extends('layouts.site')

@section('title', 'تعديل الملف الشخصي')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/profile-edit.css') }}">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
  <main class="page">
    <div class="container edit-page">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m15 18-6-6 6-6"></path></svg>
        <a href="{{ route('profile.show') }}">الملف الشخصي</a>
        <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m15 18-6-6 6-6"></path></svg>
        <span class="current">تعديل الملف الشخصي</span>
      </nav>

      <div class="edit-head">
        <h1>تعديل الملف الشخصي</h1>
        <p>تحديث بياناتك الشخصية وموقعك الجغرافي التقريبي في الحي</p>
      </div>

      <form class="edit-card" id="edit-form" method="POST" action="{{ route('profile.update') }}" novalidate>
        @csrf
        @method('PATCH')

        {{-- رسالة النجاح بعد الحفظ --}}
        @if (session('status') === 'profile-updated')
        <div class="privacy-box" role="status">
          <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>
          <div>تم حفظ التغييرات</div>
        </div>
        @endif

          <div class="form-group @error('full_name') has-error @enderror">
            <label class="form-label" for="edit-name">الاسم الكامل</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              <input type="text" id="edit-name" name="full_name" value="{{ old('full_name', $user->full_name) }}" autocomplete="name">
            </div>
            <p class="form-error">@error('full_name'){{ $message }}@enderror</p>
          </div>

        <div class="form-grid">
          <div class="form-group @error('email') has-error @enderror">
            <label class="form-label" for="edit-email">البريد الإلكتروني</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="4"></circle><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"></path></svg>
              <input type="email" id="edit-email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email">
            </div>
            <p class="form-error">@error('email'){{ $message }}@enderror</p>
          </div>
          <div class="form-group @error('phone') has-error @enderror">
            <label class="form-label" for="edit-phone">رقم الهاتف</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect><path d="M12 18h.01"></path></svg>
              <input type="tel" id="edit-phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="09xxxxxxxx" maxlength="10" autocomplete="tel">
            </div>
            <p class="form-error">@error('phone'){{ $message }}@enderror</p>
          </div>
        </div>

          <div class="form-group @error('address') has-error @enderror">
            <label class="form-label" for="edit-area">المنطقة (تقريبية)</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
              <input type="text" id="edit-area" name="address" value="{{ old('address', $user->address) }}" placeholder="مثال: حي المزة، دمشق">
            </div>
            <p class="form-error">@error('address'){{ $message }}@enderror</p>
            <p class="form-note"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>يظهر موقعك بشكل تقريبي، ولا تظهر بيانات تواصلك إلا بعد قبول طلب.</p>
          </div>

          <div class="form-group @error('city') has-error @enderror">
            <label class="form-label" for="edit-city">المدينة</label>
            <div class="input-wrap">
              <x-icon name="home" />
              <input type="text" id="edit-city" name="city" value="{{ old('city', $user->city) }}" placeholder="مثال: دمشق">
            </div>
            <p class="form-error">@error('city'){{ $message }}@enderror</p>
          </div>

          {{-- الموقع التقريبي على الخريطة: بيستخدم لحساب المسافة بالبحث --}}
          <div class="form-group @error('latitude') has-error @enderror">
            <div class="form-label-row">
              <span class="form-label">موقعك التقريبي على الخريطة</span>
              <button type="button" class="form-hint-link js-locate-me">استخدم موقعي الحالي</button>
            </div>
            <div class="map-box js-location-picker" data-lat-input="edit-lat" data-lng-input="edit-lng"></div>
            <input type="hidden" id="edit-lat" name="latitude" value="{{ old('latitude', $user->latitude) }}">
            <input type="hidden" id="edit-lng" name="longitude" value="{{ old('longitude', $user->longitude) }}">
            <p class="form-error">@error('latitude'){{ $message }}@enderror</p>
            <p class="form-note"><x-icon name="info" />اضغط على الخريطة لتحديد منطقتك. نحفظ الموقع مقرّباً ولا يظهر لأحد بدقة.</p>
          </div>

        <section class="edit-section" aria-labelledby="password-title">
          <h2 id="password-title"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>تغيير كلمة المرور</h2>
          <p>اترك هذه الحقول فارغة إذا كنت لا ترغب بتغيير كلمة المرور الحالية</p>

          <div class="form-grid form-grid--3">
          <div class="form-group @error('current_password') has-error @enderror">
            <label class="form-label" for="edit-current">كلمة المرور الحالية</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <input type="password" id="edit-current" name="current_password" placeholder="••••••••" autocomplete="current-password">
              <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
            </div>
            <p class="form-error">@error('current_password'){{ $message }}@enderror</p>
          </div>
          <div class="form-group @error('password') has-error @enderror">
            <label class="form-label" for="edit-password">كلمة المرور الجديدة</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <input type="password" id="edit-password" name="password" placeholder="8 أحرف على الأقل" autocomplete="new-password">
              <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
            </div>
            <p class="form-error">@error('password'){{ $message }}@enderror</p>
          </div>
          <div class="form-group">
            <label class="form-label" for="edit-confirm">تأكيد كلمة المرور الجديدة</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <input type="password" id="edit-confirm" name="password_confirmation" placeholder="••••••••" autocomplete="new-password">
              <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
            </div>
            <p class="form-error"></p>
          </div>
          </div>
        </section>

        <div class="edit-actions">
          <button type="submit" class="btn btn--primary"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M20 6 9 17l-5-5"></path></svg>حفظ التغييرات</button>
          <a href="{{ route('profile.show') }}" class="btn btn--soft">إلغاء</a>
        </div>
      </form>
    </div>
  </main>
@endsection

@push('libraries')
  <!-- مكتبة الخرائط Leaflet -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/location-picker.js') }}"></script>
  <script src="{{ asset('assets/js/validation.js') }}"></script>
  <script src="{{ asset('assets/js/profile-edit.js') }}"></script>
@endpush
