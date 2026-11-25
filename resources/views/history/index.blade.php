{{-- صفحة سجل التعاملات --}}
@extends('layouts.site')

@section('title', 'سجل التعاملات')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/history.css') }}">
@endpush

@php
  $typeOptions = ['' => 'الكل', 'sale' => 'بيع', 'exchange' => 'تبادل', 'rent' => 'تأجير'];
  $roleOptions = ['' => 'الكل', 'owner' => 'كمالك', 'seeker' => 'كباحث'];
  $statusStyles = ['finished' => ['مكتملة', 'status--success'], 'canceled' => ['ملغاة', ''], 'rejected' => ['مرفوضة', 'status--danger']];
@endphp

@section('content')
  <main class="page">
    <div class="container">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <span class="current">سجل التعاملات</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>سجل التعاملات</h1>
          <p>جميع عملياتك المنتهية كمالك أو كباحث في مجتمع حيك</p>
        </div>
        <div class="stat-card stat-card--small">
          <span class="stat-card__icon"><x-icon name="check-circle" /></span>
          <div><span class="stat-card__label">العمليات الناجحة</span><strong class="stat-card__value">{{ $successCount }} عملية</strong></div>
        </div>
      </div>

      <!-- الفلاتر -->
      <form class="panel history-filters" method="GET" action="{{ route('history.index') }}">
        <fieldset class="segmented-radio">
          <legend><x-icon name="repeat" />نوع التعامل:</legend>
          @foreach ($typeOptions as $key => $label)
            <label><input type="radio" name="type" value="{{ $key }}" class="js-auto-submit" @checked(($filters['type'] ?? '') === $key)><span>{{ $label }}</span></label>
          @endforeach
        </fieldset>

        <fieldset class="segmented-radio">
          <legend><x-icon name="user" />دورك:</legend>
          @foreach ($roleOptions as $key => $label)
            <label><input type="radio" name="role" value="{{ $key }}" class="js-auto-submit" @checked(($filters['role'] ?? '') === $key)><span>{{ $label }}</span></label>
          @endforeach
        </fieldset>

        <label class="history-filters__period">
          <span><x-icon name="calendar" />النطاق:</span>
          <select class="select select--sm js-auto-submit" name="months">
            <option value="">كل الفترات</option>
            <option value="1" @selected($filters['months'] === 1)>آخر شهر</option>
            <option value="6" @selected($filters['months'] === 6)>آخر 6 أشهر</option>
            <option value="12" @selected($filters['months'] === 12)>آخر سنة</option>
          </select>
        </label>

        <a href="{{ route('history.index') }}" class="icon-btn" aria-label="إعادة تعيين الفلاتر"><x-icon name="refresh" /></a>
      </form>

      <div class="table-wrap">
        <table class="data-table history-table">
          <thead>
            <tr><th>الغرض</th><th>الطرف الآخر</th><th>نوع التعامل</th><th>دورك</th><th>التاريخ</th><th>الحالة النهائية</th><th>التقييم</th></tr>
          </thead>
          <tbody>
            @forelse ($deals as $deal)
              @php
                $item = $deal->item;
                $isOwner = $item->owner_id === $user->id;
                $other = $isOwner ? $deal->requester : $item->owner;
                [$statusText, $statusClass] = $statusStyles[$deal->status];
              @endphp
              <tr>
                <td>
                  <a href="{{ route('requests.show', $deal) }}" class="thumb-cell">
                    @if ($item->mainImageUrl())
                      <img src="{{ $item->mainImageUrl() }}" alt="">
                    @else
                      <span class="thumb-cell__empty"></span>
                    @endif
                    <span>
                      <strong>{{ $item->title }}</strong>
                      <span class="data-table__muted"><x-icon name="map-pin" />{{ $item->areaLabel() }}</span>
                    </span>
                  </a>
                </td>
                <td>
                  <strong>{{ $other->full_name }}</strong>
                  <span class="data-table__muted">★ {{ number_format($other->rate, 1) }}</span>
                </td>
                <td><span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span></td>
                <td><span @class(['tag', 'tag--accent' => $isOwner])>{{ $isOwner ? 'مالك' : 'باحث' }}</span></td>
                <td class="muted">{{ \App\Support\Dates::full($deal->updated_at) }}</td>
                <td><span class="status {{ $statusClass }}">{{ $statusText }}</span></td>
                <td>
                  @if ($deal->status !== 'finished')
                    <span class="muted">—</span>
                  @elseif (isset($myReviews[$deal->id]))
                    <span class="rating-cell"><x-icon name="star" />{{ $myReviews[$deal->id] }}.0</span>
                  @else
                    <a href="{{ route('requests.show', $deal) }}#review" class="btn btn--primary btn--xs"><x-icon name="star" />قيّم الآن</a>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="muted">لا توجد تعاملات منتهية مطابقة.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @include('partials.pagination', ['paginator' => $deals, 'label' => 'عملية مسجلة'])

      <aside class="history-banner">
        <x-icon name="shield" />
        <div>
          <strong>تقييمك يعزز ثقة الجيران في حيك!</strong>
          <p>التقييمات المتبادلة بعد إتمام كل عملية تسهم في بناء مجتمع آمن وموثوق لتبادل وإعارة الأغراض.</p>
        </div>
      </aside>
    </div>
  </main>
@endsection
