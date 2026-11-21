{{-- هيدر المستخدم المسجّل --}}
  <header class="site-header">
    <div class="container site-header__inner">
      <a href="{{ route('home') }}" class="brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="">
        <span>غرضك قريب</span>
      </a>

      <nav class="main-nav" aria-label="القائمة الرئيسية">
        <a href="{{ route('home') }}">الرئيسية</a>
        <a href="{{ route('home') }}#nearby">قريب منك</a>
        <a href="{{ route('home') }}#categories-section">التصنيفات</a>
        <a href="{{ route('home') }}#how-it-works">كيف يعمل الموقع</a>
      </nav>

      <div class="header-actions">
        <button class="btn btn--primary btn--sm js-soon" type="button" data-message="صفحة إضافة غرض ضمن المرحلة القادمة">
          <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg><span class="btn-label">أضف غرضاً</span>
        </button>
        <a href="#notifications" class="icon-btn" aria-label="الإشعارات">
          <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg><span class="dot"></span>
        </a>
        <a href="{{ route('profile.show') }}" class="avatar" aria-label="الملف الشخصي">ي</a>
        <button class="icon-btn menu-toggle" type="button" aria-label="فتح القائمة" aria-expanded="false">
          <svg class="icon" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"  xmlns="http://www.w3.org/2000/svg"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>
        </button>
      </div>
    </div>
  </header>
