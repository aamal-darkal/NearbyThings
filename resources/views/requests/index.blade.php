{{-- صفحة طلباتي (الطلبات اللي أرسلها المستخدم) --}}
@extends('layouts.site')

@section('title', 'طلباتي')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/requests.css') }}">
@endpush

@php
  $tabs = ['all' => 'الكل', 'waiting' => 'قيد الانتظار', 'approved' => 'مقبول', 'received' => 'مستلم', 'rejected' => 'مرفوض', 'canceled' => 'ملغى', 'finished' => 'مكتمل'];
@endphp

@section('content')
  <main class="page">
    <div class="container container--narrow">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <span class="current">طلباتي</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>طلباتي</h1>
          <p>متابعة الطلبات التي أرسلتها للجيران والتنسيق معهم</p>
        </div>
        @if ($counts['waiting'] > 0)
          <span class="tag tag--accent"><x-icon name="clock" />لديك {{ $counts['waiting'] }} طلبات بانتظار رد الجار</span>
        @endif
      </div>

      @include('requests.partials.nav')

      <nav class="chips requests-tabs" aria-label="تصفية حسب الحالة">
        @foreach ($tabs as $key => $label)
          @if ($key === 'all' || $counts[$key] > 0)
            <a href="{{ route('requests.index', ['tab' => $key]) }}" @class(['chip', 'is-active' => $tab === $key])>{{ $label }} ({{ $counts[$key] }})</a>
          @endif
        @endforeach
      </nav>

      <section class="request-list">
        @forelse ($requests as $deal)
          @php $item = $deal->item; @endphp
          <article class="request-card">
            <a href="{{ route('items.show', $item) }}" class="request-card__media">
              @if ($item->mainImageUrl())
                <img src="{{ $item->mainImageUrl() }}" alt="{{ $item->title }}" loading="lazy">
              @endif
              <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
            </a>

            <div class="request-card__body">
              <p class="request-card__meta">
                @if ($deal->status === 'finished')
                  عملية منتهية • اكتملت {{ \App\Support\Dates::ago($deal->updated_at) }}
                @elseif ($item->transaction_type === 'exchange')
                  <strong class="request-card__price">طلب تبادل غرض</strong> • أرسل {{ \App\Support\Dates::ago($deal->request_date) }}
                @else
                  <strong class="request-card__price">{{ $item->priceLabel() }}</strong> • أرسل {{ \App\Support\Dates::ago($deal->request_date) }}
                @endif
              </p>
              <h2 class="request-card__title"><a href="{{ route('requests.show', $deal) }}">{{ $item->title }}</a></h2>

              @if ($deal->rental)
                @include('requests.partials.rent-line', ['deal' => $deal])
              @endif
              @if ($deal->exchangeOffer)
                <span class="request-line muted"><x-icon name="repeat" />عرضت مقابله: {{ $deal->exchangeOffer->label() }}</span>
              @endif

              <span class="request-line">
                <strong>{{ $item->owner->full_name }}</strong>
                <span class="rating-inline"><x-icon name="star" />{{ number_format($item->owner->rate, 1) }}</span>
                • <x-icon name="map-pin" />{{ $item->areaLabel() }}
              </span>

              @if ($deal->status === 'approved' && $deal->appointment && $deal->appointment->status !== 'canceled')
                <span class="request-note request-note--success"><x-icon name="calendar" />حدد المالك وقت التسليم: {{ \App\Support\Dates::dateTime($deal->appointment->meeting_date) }}</span>
              @endif
              @if ($deal->status === 'rejected' && $deal->owner_note)
                <span class="request-note request-note--danger"><x-icon name="info" />ملاحظة المالك: {{ $deal->owner_note }}</span>
              @endif
            </div>

            <div class="request-card__side">
              <span class="status {{ $deal->statusClass() }}">{{ $deal->statusLabel() }}</span>

              @if ($deal->status === 'waiting')
                <form method="POST" action="{{ route('requests.cancel', $deal) }}" class="js-confirm" data-confirm="هل تريد إلغاء هذا الطلب؟">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn--text-danger btn--sm">إلغاء الطلب</button>
                </form>
              @elseif (in_array($deal->status, ['approved', 'received']))
                <a href="{{ route('requests.show', $deal) }}" class="btn btn--primary btn--sm">عرض العملية</a>
              @elseif ($deal->status === 'finished')
                <a href="{{ route('requests.show', $deal) }}" class="btn btn--soft btn--sm">عرض العملية</a>
              @endif
            </div>
          </article>
        @empty
          <div class="empty-state">
            <x-icon name="inbox" />
            <p>لا توجد طلبات في هذا القسم.</p>
            <a href="{{ route('search') }}" class="btn btn--primary">ابحث عن غرض قريب</a>
          </div>
        @endforelse
      </section>

      <aside class="safety-banner">
        <span class="safety-banner__icon"><x-icon name="shield" /></span>
        <div>
          <h2>معاملات حيّة مباشرة وجهاً لوجه</h2>
          <p>يتم الدفع والاستلام شخصياً عند اللقاء في الحي بدون أي عمولات خفية.</p>
        </div>
      </aside>
    </div>
  </main>
@endsection
