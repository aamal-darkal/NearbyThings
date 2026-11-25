{{-- صفحة المفضلة --}}
@extends('layouts.site')

@section('title', 'المفضلة')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/favorites.css') }}">
@endpush

@php
  $filters = [null => 'الكل', 'sale' => 'بيع', 'rent' => 'تأجير', 'exchange' => 'تبادل'];
@endphp

@section('content')
  <main class="page">
    <div class="container">
      <nav class="breadcrumb" aria-label="مسار التنقل">
        <a href="{{ route('home') }}">الرئيسية</a>
        <x-icon name="chevron-left" />
        <span class="current">المفضلة</span>
      </nav>

      <div class="page-head">
        <div>
          <h1>المفضلة <span class="tag tag--accent">{{ $counts['all'] }} عناصر</span></h1>
          <p>الإعلانات التي حفظتها للرجوع إليها</p>
        </div>

        <nav class="chips" aria-label="تصفية حسب نوع العرض">
          @foreach ($filters as $key => $label)
            <a href="{{ route('favorites.index', $key ? ['type' => $key] : []) }}" @class(['chip', 'is-active' => $type == $key])>
              {{ $label }} <span class="chip__count">{{ $counts[$key ?: 'all'] }}</span>
            </a>
          @endforeach
        </nav>
      </div>

      <div class="items-grid favorites-grid">
        @forelse ($favorites as $item)
          @php $available = $item->state === 'active' && ! $item->owner->is_blocked; @endphp
          <article @class(['item-card', 'js-favorite-card', 'is-unavailable' => ! $available])>
            <div class="item-card__media">
              @if ($item->mainImageUrl())
                <img src="{{ $item->mainImageUrl() }}" alt="{{ $item->title }}" loading="lazy">
              @else
                <span class="item-card__placeholder"><x-icon name="image" /></span>
              @endif
              <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
              @unless ($available)
                <span class="item-card__overlay"><span>لم يعد متاحاً</span></span>
              @endunless
            </div>
            <button class="fav-btn js-fav is-active" type="button" data-url="{{ route('favorites.toggle', $item) }}"
                    aria-pressed="true" aria-label="إزالة من المفضلة"><x-icon name="heart" /></button>
            <div class="item-card__body">
              <h3 class="item-card__title"><a href="{{ route('items.show', $item) }}">{{ $item->title }}</a></h3>
              <span class="item-card__distance"><x-icon name="navigation" />{{ $item->areaLabel() }}</span>
              <div class="item-card__footer">
                <span class="item-card__price">
                  @if ($item->transaction_type === 'exchange')
                    <x-icon name="repeat" /> متاح للتبادل
                  @else
                    {{ $item->priceNumber() }}
                    <small>ل.س @if ($item->transaction_type === 'rent') / {{ $item->rentUnitLabel() }} @endif</small>
                  @endif
                </span>
                @if ($available)
                  <span class="tag tag--accent">عرض التفاصيل</span>
                @else
                  <span class="tag">غير متاح</span>
                @endif
              </div>
            </div>
          </article>
        @empty
          <div class="empty-state">
            <x-icon name="heart" />
            <p>لم تحفظ أي إعلان بعد. اضغط على القلب في أي إعلان لحفظه هنا.</p>
            <a href="{{ route('home') }}" class="btn btn--primary">تصفح الإعلانات</a>
          </div>
        @endforelse
      </div>
    </div>
  </main>
@endsection
