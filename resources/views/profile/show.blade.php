{{-- صفحة الملف الشخصي --}}
@extends('layouts.site')

@section('title', 'الملف الشخصي')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/profile.css') }}">
@endpush

@section('content')
  <main class="page">
    <div class="container">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <span class="current">الملف الشخصي</span>
      </nav>

      <!-- بطاقة المستخدم -->
      <section class="profile-card">
        <span class="avatar">{{ $user->initial() }}</span>
        <div>
          <h1>{{ $user->full_name }}</h1>
          <p class="profile-card__area"><x-icon name="map-pin" /><span>{{ $user->areaLabel() ?: 'لم تحدد منطقتك بعد' }}</span></p>
          <div class="profile-stats">
            <span class="stat"><x-icon name="star" class="icon--star" /><strong>{{ number_format($user->rate, 1) }}</strong> التقييم العام</span>
            <span class="stat"><x-icon name="package" class="icon--green" /><strong>{{ $items->count() }}</strong> إعلان نشط</span>
            <span class="stat"><x-icon name="calendar" class="icon--green" />عضو منذ {{ \App\Support\Dates::monthYear($user->created_at) }}</span>
          </div>
        </div>
        <div class="profile-card__actions">
          <a href="{{ route('profile.edit') }}" class="btn btn--primary"><x-icon name="pencil" />تعديل الملف الشخصي</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn--soft"><x-icon name="log-out" />تسجيل الخروج</button>
          </form>
        </div>
      </section>

      <!-- أقسام الحساب -->
      <nav class="account-nav profile-nav" aria-label="أقسام الحساب">
        <a href="{{ route('profile.show') }}" class="pill is-active"><x-icon name="user" />ملفي</a>
        <a href="{{ route('items.mine') }}" class="pill"><x-icon name="package" />إعلاناتي</a>
        <a href="{{ route('requests.index') }}" class="pill"><x-icon name="send" />طلباتي</a>
        <a href="{{ route('requests.incoming') }}" class="pill"><x-icon name="inbox" />الطلبات الواردة</a>
        <a href="{{ route('favorites.index') }}" class="pill"><x-icon name="heart" />المفضلة</a>
      </nav>

      <!-- التبويبات -->
      <div class="tabs">
        <button class="tab is-active" type="button" data-panel="panel-items">
          الإعلانات النشطة <span class="tab__count">{{ $items->count() }}</span>
        </button>
      </div>

      <section class="tab-panel" id="panel-items">
        <div class="items-grid">
          @forelse ($items as $item)
            @include('partials.item-card', ['item' => $item])
          @empty
            <div class="empty-state">
              <x-icon name="package" />
              <p>لا توجد إعلانات نشطة حالياً.</p>
            </div>
          @endforelse
        </div>
      </section>
    </div>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/profile.js') }}"></script>
@endpush
