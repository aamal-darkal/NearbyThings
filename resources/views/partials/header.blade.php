{{-- هيدر الموقع: بيتغير حسب إذا المستخدم مسجّل دخول أو زائر --}}
  <header class="site-header">
    <div class="container site-header__inner">
      <a href="{{ route('home') }}" class="brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="">
        <span>غرضك قريب</span>
      </a>

      <nav class="main-nav" aria-label="القائمة الرئيسية">
        <a href="{{ route('home') }}" @class(['is-active' => request()->routeIs('home')])>الرئيسية</a>
        <a href="{{ route('home') }}#nearby">قريب منك</a>
        <a href="{{ route('home') }}#categories-section">التصنيفات</a>
        <a href="{{ route('home') }}#how-it-works">كيف يعمل الموقع</a>
        @if (auth()->user()?->is_admin)
          <a href="{{ route('admin.dashboard') }}">لوحة المشرف</a>
        @endif
      </nav>

      <div class="header-actions">
        @auth
          <a href="{{ route('items.create') }}" class="btn btn--primary btn--sm">
            <x-icon name="plus" /><span class="btn-label">أضف غرضاً</span>
          </a>
          <a href="{{ route('profile.show') }}" class="icon-btn" aria-label="الإشعارات">
            <x-icon name="bell" />
            @if (auth()->user()->unreadNotificationsCount() > 0)
              <span class="dot"></span>
            @endif
          </a>
          <a href="{{ route('profile.show') }}" class="avatar" aria-label="الملف الشخصي">{{ auth()->user()->initial() }}</a>
        @else
          <a href="{{ route('login') }}" class="btn btn--soft btn--sm">تسجيل الدخول</a>
          <a href="{{ route('register') }}" class="btn btn--primary btn--sm">إنشاء حساب</a>
        @endauth
        <button class="icon-btn menu-toggle" type="button" aria-label="فتح القائمة" aria-expanded="false">
          <x-icon name="menu" />
        </button>
      </div>
    </div>
  </header>
