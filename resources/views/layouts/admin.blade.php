{{-- قالب لوحة المشرف: قائمة جانبية + شريط علوي + محتوى الصفحة --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title') - لوحة المشرف - غرضك قريب</title>
  <link rel="icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap">

  <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/base.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  @stack('styles')
</head>
<body class="admin-body">

  <div class="admin-shell">
    <!-- القائمة الجانبية -->
    <aside class="admin-sidebar" id="admin-sidebar">
      <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="">
        <span>
          <strong>غرضك قريب</strong>
          <small>لوحة المشرف</small>
        </span>
      </a>

      <nav class="admin-nav" aria-label="قائمة المشرف">
        <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')])><x-icon name="grid" />لوحة التحكم</a>
        <a href="{{ route('admin.listings.index') }}" @class(['is-active' => request()->routeIs('admin.listings.*')])><x-icon name="repeat" />الإعلانات والطلبات</a>
        <a href="{{ route('admin.reports.index') }}" @class(['is-active' => request()->routeIs('admin.reports.*')])><x-icon name="flag" />البلاغات</a>
        <a href="{{ route('admin.categories.index') }}" @class(['is-active' => request()->routeIs('admin.categories.*')])><x-icon name="shapes" />التصنيفات وحالات الغرض</a>
        <a href="{{ route('admin.suggestions.index') }}" @class(['is-active' => request()->routeIs('admin.suggestions.*')])><x-icon name="lightbulb" />اقتراحات التصنيفات</a>
        <a href="{{ route('admin.users.index') }}" @class(['is-active' => request()->routeIs('admin.users.*')])><x-icon name="users" />المستخدمون</a>
      </nav>

      <div class="admin-user">
        <div class="admin-user__info">
          <span class="avatar">{{ auth()->user()->initial() }}</span>
          <div>
            <strong>{{ auth()->user()->full_name }}</strong>
            <small>{{ auth()->user()->email }}</small>
          </div>
        </div>
        <a href="{{ route('home') }}" class="admin-user__link"><x-icon name="home" />العودة للموقع</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="admin-user__logout"><x-icon name="log-out" />تسجيل الخروج</button>
        </form>
      </div>
    </aside>

    <div class="admin-main">
      <!-- الشريط العلوي -->
      <header class="admin-topbar">
        <button class="icon-btn menu-toggle" type="button" aria-label="فتح القائمة" aria-expanded="false"><x-icon name="menu" /></button>
        <span class="admin-topbar__title">منصة إدارة غرضك قريب</span>
        <div class="header-actions">
          <a href="{{ route('notifications.index') }}" class="icon-btn" aria-label="الإشعارات">
            <x-icon name="bell" />
            @if (auth()->user()->unreadNotificationsCount() > 0)<span class="dot"></span>@endif
          </a>
          <span class="avatar">{{ auth()->user()->initial() }}</span>
        </div>
      </header>

      <main class="admin-content">
        @yield('content')
      </main>
    </div>
  </div>

  @stack('modals')

  <div @class(['toast', 'toast--error' => session('toast_type') === 'error']) id="toast" role="status" aria-live="polite" data-message="{{ session('toast') }}" hidden></div>

  <script src="{{ asset('assets/js/main.js') }}"></script>
  <script src="{{ asset('assets/js/admin.js') }}"></script>
  @stack('scripts')
</body>
</html>
