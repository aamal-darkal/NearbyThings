{{-- صفحة إنشاء حساب --}}
@extends('layouts.site')

@section('title', 'إنشاء حساب')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
@endpush

@section('header')
    @include('partials.header-guest')
@endsection

@section('content')
  <main class="auth-page">
    <div class="container">
      <section class="auth-card">
        <div class="auth-intro">
          <div class="auth-intro__logo"><img src="{{ asset('assets/images/logo.png') }}" alt=""></div>
        </div>
        <nav class="auth-tabs" aria-label="نوع النموذج">
          <a href="{{ route('login') }}">تسجيل الدخول</a>
          <a href="{{ route('register') }}" class="is-active">إنشاء حساب</a>
        </nav>

        <form id="register-form" method="POST" action="{{ route('register') }}" novalidate>
          @csrf

          <div class="form-group @error('full_name') has-error @enderror">
            <label class="form-label" for="reg-name">الاسم الكامل</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              <input type="text" id="reg-name" name="full_name" value="{{ old('full_name') }}" placeholder="مثال: محمد أحمد" autocomplete="name">
            </div>
            <p class="form-error">@error('full_name'){{ $message }}@enderror</p>
          </div>

          <div class="form-group @error('email') has-error @enderror">
            <label class="form-label" for="reg-email">البريد الإلكتروني</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="4"></circle><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"></path></svg>
              <input type="email" id="reg-email" name="email" value="{{ old('email') }}" placeholder="مثال: rawda@gmail.com" autocomplete="email">
            </div>
            <p class="form-error">@error('email'){{ $message }}@enderror</p>
          </div>

          <div class="form-group @error('phone') has-error @enderror">
            <label class="form-label" for="reg-phone">رقم الهاتف</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"></rect><path d="M12 18h.01"></path></svg>
              <input type="tel" id="reg-phone" name="phone" value="{{ old('phone') }}" placeholder="09xxxxxxxx" maxlength="10" autocomplete="tel">
            </div>
            <p class="form-error">@error('phone'){{ $message }}@enderror</p>
          </div>

          <div class="form-group @error('password') has-error @enderror">
            <label class="form-label" for="reg-password">كلمة المرور</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <input type="password" id="reg-password" name="password" placeholder="8 أحرف على الأقل" autocomplete="new-password">
              <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
            </div>
            <p class="form-error">@error('password'){{ $message }}@enderror</p>
          </div>

          <div class="form-group">
            <label class="form-label" for="reg-confirm">تأكيد كلمة المرور</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <input type="password" id="reg-confirm" name="password_confirmation" placeholder="••••••••" autocomplete="new-password">
              <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
            </div>
            <p class="form-error"></p>
          </div>

          <div class="form-group @error('address') has-error @enderror">
            <label class="form-label" for="reg-area">المنطقة (تقريبية)</label>
            <div class="input-wrap input-wrap--accent">
              <input type="text" id="reg-area" name="address" value="{{ old('address') }}" placeholder="مثال: حي المزة، دمشق">
            </div>
            <p class="form-error">@error('address'){{ $message }}@enderror</p>
            <p class="form-note"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path></svg>نستخدم منطقتك التقريبية فقط لاقتراح الأغراض القريبة منك ولا نشارك موقعك الدقيق.</p>
          </div>

          <button type="submit" class="btn btn--primary btn--lg btn--block">
            إنشاء حساب <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
          </button>
        </form>

        <p class="auth-switch">لديك حساب بالفعل؟<a href="{{ route('login') }}">سجّل دخولك</a></p>
      </section>

      <p class="auth-trust"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>بيئة موثوقة للتواصل والتبادل بين أفراد الحي الواحد</p>
    </div>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/validation.js') }}"></script>
  <script src="{{ asset('assets/js/register.js') }}"></script>
@endpush
