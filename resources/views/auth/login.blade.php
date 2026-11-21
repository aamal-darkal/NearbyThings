{{-- صفحة تسجيل الدخول --}}
@extends('layouts.site')

@section('title', 'تسجيل الدخول')

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
          <a href="{{ route('login') }}" class="is-active">تسجيل الدخول</a>
          <a href="{{ route('register') }}">إنشاء حساب</a>
        </nav>

        <form id="login-form" method="POST" action="{{ route('login') }}" novalidate>
          @csrf

          {{-- رسالة النجاح --}}
          @if (session('status'))
          <div class="privacy-box" role="status">
            <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>
            <div>{{ session('status') }}</div>
          </div>
          @endif

          <div class="form-group @error('email') has-error @enderror">
            <label class="form-label" for="login-email">البريد الإلكتروني</label>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="4"></circle><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"></path></svg>
              <input type="email" id="login-email" name="email" value="{{ old('email') }}" placeholder="مثال: rawda@gmail.com" autocomplete="email">
            </div>
            <p class="form-error">@error('email'){{ $message }}@enderror</p>
          </div>

          <div class="form-group @error('password') has-error @enderror">
            <div class="form-label-row">
              <label class="form-label" for="login-password">كلمة المرور</label>
              <a href="{{ route('password.request') }}" class="form-hint-link">نسيت كلمة المرور؟</a>
            </div>
            <div class="input-wrap">
              <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              <input type="password" id="login-password" name="password" placeholder="••••••••" autocomplete="current-password">
              <button type="button" class="toggle-password" aria-label="إظهار كلمة المرور"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
            </div>
            <p class="form-error">@error('password'){{ $message }}@enderror</p>
          </div>

          <label class="form-check">
            <input type="checkbox" name="remember" @checked(old('remember'))>
            <span>تذكرني</span>
          </label>

          <button type="submit" class="btn btn--primary btn--lg btn--block">
            تسجيل الدخول <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
          </button>
        </form>

        <p class="auth-switch">ليس لديك حساب؟<a href="{{ route('register') }}">أنشئ حساباً الآن</a></p>
      </section>

      <p class="auth-trust"><svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>بيئة موثوقة للتواصل والتبادل بين أفراد الحي الواحد</p>
    </div>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/validation.js') }}"></script>
  <script src="{{ asset('assets/js/login.js') }}"></script>
@endpush
