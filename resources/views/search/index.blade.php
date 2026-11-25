{{-- صفحة نتائج البحث والفلترة مع الخريطة --}}
@extends('layouts.site')

@section('title', 'نتائج البحث')

@push('styles')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
@endpush

@php
  $sortOptions = ['nearest' => 'الأقرب إليك أولاً (المسافة)', 'newest' => 'الأحدث أولاً', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى'];
  $radiusLabels = [1 => '1 كم (حارتي)', 5 => '5 كم', 10 => '10 كم', 20 => '20 كم (كل المدينة)'];
@endphp

@section('content')
  {{-- نموذج واحد لكل الصفحة: البحث + الفلاتر + الترتيب --}}
  <form method="GET" action="{{ route('search') }}" id="search-form">
    <!-- شريط البحث -->
    <section class="search-top">
      <div class="container">
        <nav class="breadcrumb" aria-label="مسار التنقل">
          <a href="{{ route('home') }}">الرئيسية</a>
          <x-icon name="chevron-left" />
          <span class="current">نتائج البحث</span>
          @if ($filters['keyword'] !== '')
            <span class="tag tag--accent">«{{ $filters['keyword'] }}»</span>
          @endif
        </nav>

        <div class="search-bar">
          <div class="search-bar__field search-bar__field--keyword">
            <x-icon name="search" />
            <label class="sr-only" for="search-q">كلمة البحث</label>
            <input type="search" id="search-q" name="q" value="{{ $filters['keyword'] }}" placeholder="عن ماذا تبحث؟ مثال: دراجة هوائية">
          </div>

          <div class="search-bar__field search-bar__field--location">
            <x-icon name="map-pin" />
            <div class="search-bar__stack">
              <span class="muted">الموقع المحدد</span>
              <strong>{{ $center['label'] }}</strong>
            </div>
            <button type="button" class="tag tag--accent js-locate" aria-label="استخدم موقعي الحالي"><x-icon name="crosshair" />حددني</button>
            {{-- بيتعبوا من زر حددني --}}
            <input type="hidden" name="lat" id="search-lat" value="{{ request('lat') }}">
            <input type="hidden" name="lng" id="search-lng" value="{{ request('lng') }}">
          </div>

          <button type="submit" class="btn btn--primary btn--lg"><x-icon name="search" />تحديث البحث</button>
        </div>

        <div class="quick-links">
          <span class="muted">تصفح سريع:</span>
          @foreach ($categories->take(5) as $category)
            <a href="{{ route('search', ['category' => $category->id]) }}" @class(['chip', 'is-active' => $filters['categoryId'] === $category->id])>{{ $category->category_name }}</a>
          @endforeach
        </div>
      </div>
    </section>

    <div class="container search-layout">
      <!-- الفلاتر -->
      <aside class="panel filters">
        <div class="filters__head">
          <h2><x-icon name="sliders" />تصفية النتائج</h2>
          <a href="{{ route('search') }}" class="link">إعادة تعيين</a>
        </div>

        <fieldset class="filter-group">
          <legend>نوع العملية <span class="muted">متعدد الخيارات</span></legend>
          @foreach (['sale' => 'بيع', 'rent' => 'تأجير', 'exchange' => 'تبادل'] as $key => $label)
            <label class="filter-check">
              <input type="checkbox" name="types[]" value="{{ $key }}" @checked(in_array($key, $filters['types']))>
              <span>{{ $label }}</span>
              <span class="filter-check__count">{{ $typeCounts[$key] }}</span>
            </label>
          @endforeach
        </fieldset>

        <fieldset class="filter-group">
          <legend>نطاق المسافة <span class="tag tag--accent">حتى {{ $filters['radius'] }} كم</span></legend>
          <div class="radius-options">
            @foreach ($radiusOptions as $option)
              <label class="choice-pill">
                <input type="radio" name="radius" value="{{ $option }}" @checked($filters['radius'] === $option)>
                <span>{{ $radiusLabels[$option] }}</span>
              </label>
            @endforeach
          </div>
        </fieldset>

        <div class="filter-group">
          <label class="filter-group__label" for="filter-category">التصنيف</label>
          <select class="select" id="filter-category" name="category">
            <option value="">كل التصنيفات</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected($filters['categoryId'] === $category->id)>{{ $category->category_name }}</option>
            @endforeach
          </select>
        </div>

        <fieldset class="filter-group">
          <legend>نطاق السعر (ل.س)</legend>
          <div class="price-range">
            <label>
              <span class="muted">من</span>
              <input class="input" type="number" name="min_price" min="0" value="{{ $filters['minPrice'] }}" placeholder="0">
            </label>
            <label>
              <span class="muted">إلى</span>
              <input class="input" type="number" name="max_price" min="0" value="{{ $filters['maxPrice'] }}" placeholder="بدون حد">
            </label>
          </div>
        </fieldset>

        <fieldset class="filter-group">
          <legend>حالة الغرض</legend>
          @foreach ($conditions as $condition)
            <label class="filter-check filter-check--plain">
              <input type="checkbox" name="conditions[]" value="{{ $condition->id }}" @checked(in_array($condition->id, $filters['conditionIds']))>
              <span>{{ $condition->condition_name }}</span>
            </label>
          @endforeach
        </fieldset>

        <button type="submit" class="btn btn--primary btn--block"><x-icon name="check" />تطبيق الفلاتر</button>
      </aside>

      <!-- النتائج -->
      <section class="results">
        <div class="panel results__head">
          <h1>المعروضات القريبة <span class="tag tag--accent">{{ $items->total() }} نتيجة مطابقة</span></h1>
          <div class="results__tools">
            <label class="muted" for="search-sort">الترتيب:</label>
            <select class="select select--sm js-auto-submit" id="search-sort" name="sort">
              @foreach ($sortOptions as $key => $label)
                <option value="{{ $key }}" @selected($filters['sort'] === $key)>{{ $label }}</option>
              @endforeach
            </select>
            <div class="view-toggle" role="group" aria-label="طريقة العرض">
              <button type="button" class="is-active js-view" data-view="grid"><x-icon name="grid" />شبكة</button>
              <button type="button" class="js-view" data-view="map"><x-icon name="map-pin" />خريطة الحي</button>
            </div>
          </div>
        </div>

        {{-- الخريطة: دائرة البحث + مواقع تقريبية للأغراض --}}
        <div class="panel results-map" id="results-map" hidden
             data-lat="{{ $center['lat'] }}" data-lng="{{ $center['lng'] }}" data-radius="{{ $filters['radius'] }}"></div>

        <div class="items-grid search-grid" id="results-grid">
          @forelse ($items as $item)
            <div class="js-map-item" data-lat="{{ $item->latitude !== null ? round($item->latitude, 2) : '' }}"
                 data-lng="{{ $item->longitude !== null ? round($item->longitude, 2) : '' }}"
                 data-title="{{ $item->title }}" data-url="{{ route('items.show', $item) }}">
              @include('partials.item-card', ['item' => $item])
            </div>
          @empty
            <div class="empty-state">
              <x-icon name="search" />
              <p>لا توجد نتائج مطابقة. جرّب كلمة أخرى، أو وسّع نطاق المسافة، أو أزل بعض الفلاتر.</p>
            </div>
          @endforelse
        </div>

        <aside class="safety-strip">
          <x-icon name="shield" />
          <div>
            <strong>كل المعاملات وجهاً لوجه في حيك</strong>
            <p>عاين الغرض بنفسك في مكان عام قريب، وادفع نقداً بكل أمان وثقة.</p>
          </div>
        </aside>

        @include('partials.pagination', ['paginator' => $items, 'label' => 'غرض'])
      </section>
    </div>
  </form>
@endsection

@push('libraries')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('scripts')
  <script src="{{ asset('assets/js/search.js') }}"></script>
@endpush
