{{-- صفحة الإشعارات --}}
@extends('layouts.site')

@section('title', 'الإشعارات')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/notifications.css') }}">
@endpush


@section('content')
  <main class="page">
    <div class="container notifications-page">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <span class="current">الإشعارات</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>الإشعارات</h1>
          <p class="unread-line">
            @if ($unreadCount > 0)
              <span class="unread-dot"></span>لديك {{ $unreadCount }} إشعارات جديدة غير مقروءة
            @else
              لا توجد إشعارات غير مقروءة
            @endif
          </p>
        </div>
        @if ($unreadCount > 0)
          <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="link"><x-icon name="check" />تعليم الكل كمقروء</button>
          </form>
        @endif
      </div>

      <nav class="chips notifications-tabs" aria-label="تصفية الإشعارات">
        <a href="{{ route('notifications.index') }}" @class(['chip', 'is-active' => $filter === 'all'])>الكل <span class="chip__count">{{ $totalCount }}</span></a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" @class(['chip', 'is-active' => $filter === 'unread'])>غير مقروءة <span class="chip__count">{{ $unreadCount }}</span></a>
      </nav>

      <section class="panel notifications-list">
        @forelse ($groups as $period => $notifications)
          <div class="notifications-group">
            <span>{{ $period }}</span>
            <span>{{ $notifications->count() }} إشعارات</span>
          </div>

          @foreach ($notifications as $notification)
            @php
              // أيقونة ولون الإشعار حسب عنوانه
              $title = $notification->title;
              [$icon, $tone] = match (true) {
                  str_contains($title, 'رفض') || str_contains($title, 'حظر') || str_contains($title, 'حجب') => ['x-circle', 'danger'],
                  str_contains($title, 'موعد') => ['calendar', 'accent'],
                  str_contains($title, 'تقييم') => ['star', 'accent'],
                  str_contains($title, 'إرجاع') => ['refresh', ''],
                  str_contains($title, 'بلاغ') || str_contains($title, 'اعتراض') => ['flag', 'danger'],
                  str_contains($title, 'تصنيف') => ['shapes', ''],
                  str_contains($title, 'موافقة') || str_contains($title, 'تأكيد') => ['check-circle', ''],
                  str_contains($title, 'استلام') => ['handshake', 'muted'],
                  default => ['bell', ''],
              };
            @endphp
            <article @class(['notification', 'is-unread' => ! $notification->is_read])>
              @unless ($notification->is_read)<span class="notification__dot" aria-label="غير مقروء"></span>@endunless
              <span @class(['notification__icon', 'notification__icon--'.$tone => $tone !== ''])><x-icon :name="$icon" /></span>
              <div class="notification__body">
                <h2>{{ $notification->title }}</h2>
                <p>{{ $notification->message }}</p>
                @if ($notification->link || ! $notification->is_read)
                  <a href="{{ route('notifications.open', $notification) }}" class="link">{{ $notification->link ? 'عرض التفاصيل' : 'تعليم كمقروء' }} <x-icon name="chevron-left" /></a>
                @endif
              </div>
              <time class="muted">{{ \App\Support\Dates::ago($notification->created_at) }}</time>
            </article>
          @endforeach
        @empty
          <div class="empty-state">
            <x-icon name="bell" />
            <p>لا توجد إشعارات حالياً.</p>
          </div>
        @endforelse
      </section>
    </div>
  </main>
@endsection
