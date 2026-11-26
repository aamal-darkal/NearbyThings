{{-- القالب المشترك لكل صفحات الموقع (الهيدر + محتوى الصفحة + الفوتر) --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  {{-- رمز الحماية لطلبات JavaScript (مثل المفضلة) --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @hasSection('description')
  <meta name="description" content="@yield('description')">
  @endif
  <title>@yield('title') - غرضك قريب</title>
  <link rel="icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

  {{-- ملفات CSS المشتركة --}}
  <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
  {{-- ملفات CSS الخاصة بالصفحة --}}
  @stack('styles')
</head>
<body>

  {{-- الهيدر: صفحات الدخول والتسجيل بتبدله بهيدر الزائر --}}
  @section('header')
    @include('partials.header')
  @show

  @yield('content')

  @include('partials.footer')

  {{-- النوافذ المنبثقة (بعد الفوتر) --}}
  @stack('modals')

  {{-- رسالة قصيرة (Toast): رسالة الخادم بعد أي عملية بتظهر تلقائياً --}}
  <div @class(['toast', 'toast--error' => session('toast_type') === 'error']) id="toast" role="status" aria-live="polite" data-message="{{ session('toast') }}" hidden></div>

  {{-- مكتبات خاصة بالصفحة، بعدها الكود المشترك، بعدها كود الصفحة --}}
  @stack('libraries')
  <script src="{{ asset('assets/js/main.js') }}"></script>
  @stack('scripts')
</body>
</html>
