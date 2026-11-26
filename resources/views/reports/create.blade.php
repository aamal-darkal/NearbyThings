{{-- صفحة الإبلاغ عن مخالفة (على غرض أو على عملية) --}}
@extends('layouts.site')

@section('title', 'الإبلاغ عن مخالفة')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/forms.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/reports.css') }}">
@endpush

@php
  $descriptions = [
      'fraud' => 'الغرض المستلم يختلف جوهرياً عن الصور أو المعروض أو به عطل غير مصرح به',
      'prohibited' => 'مخالف لشروط الخدمة أو مواد غير مصرح بتداولها في المنصة',
      'other' => 'أي مخالفة أو مشكلة أخرى لم تذكر في الخيارات السابقة',
  ];
  $backUrl = $itemRequest ? route('requests.show', $itemRequest) : route('items.show', $item);
@endphp

@section('content')
  <main class="page">
    <div class="container">
      <form class="panel report-card" method="POST" action="{{ route('reports.store', $itemRequest ? ['request' => $itemRequest->id] : ['item' => $item->id]) }}">
        @csrf

        <div class="report-card__head">
          <span class="report-card__icon"><x-icon name="flag" /></span>
          <div>
            <h1>الإبلاغ عن مخالفة</h1>
            <p>ساعدنا في الحفاظ على مجتمع موثوق وآمن للجميع</p>
          </div>
          <a href="{{ $backUrl }}" class="icon-btn" aria-label="إغلاق"><x-icon name="x" /></a>
        </div>

        <div class="report-target">
          @if ($item->mainImageUrl())
            <img src="{{ $item->mainImageUrl() }}" alt="">
          @endif
          <div>
            <strong>{{ $item->title }}</strong>
            <span class="report-target__price">{{ $item->priceLabel() }}</span>
          </div>
          <span class="muted">{{ $item->owner->shortName() }} • {{ $item->areaLabel() }}</span>
        </div>
        @if ($itemRequest)
          <p class="alert"><x-icon name="info" />البلاغ مرتبط بعملية {{ $itemRequest->item->typeLabel() }} بتاريخ {{ \App\Support\Dates::full($itemRequest->request_date) }}</p>
        @endif

        <fieldset class="form-group @error('category') has-error @enderror">
          <legend class="form-label">سبب الإبلاغ <span class="required">*</span></legend>
          <div class="reason-options">
            @foreach ($categories as $key => $label)
              <label class="reason-option">
                <input type="radio" name="category" value="{{ $key }}" @checked(old('category', 'fraud') === $key)>
                <span>
                  <strong>{{ $label }}{{ $key === 'fraud' ? ' (عدم تطابق المواصفات أو غرض تالف)' : '' }}</strong>
                  <small>{{ $descriptions[$key] }}</small>
                </span>
              </label>
            @endforeach
          </div>
          <p class="form-error">@error('category'){{ $message }}@enderror</p>
        </fieldset>

        <div class="form-group @error('reason') has-error @enderror">
          <div class="form-label-row">
            <label class="form-label" for="report-reason">تفاصيل البلاغ <span class="required">*</span></label>
            <span class="muted" id="reason-count">0 / 500</span>
          </div>
          <textarea class="textarea" id="report-reason" name="reason" maxlength="500" rows="5" placeholder="يرجى كتابة تفاصيل المشكلة أو المخالفة بوضوح لمساعدة فريق الإشراف...">{{ old('reason') }}</textarea>
          <p class="form-error">@error('reason'){{ $message }}@enderror</p>
        </div>

        <p class="alert alert--plain"><x-icon name="shield" />سيراجع المشرف بلاغك ويتخذ الإجراء المناسب في أقرب وقت</p>

        <div class="report-card__actions">
          <button type="submit" class="btn btn--primary"><x-icon name="send" />إرسال البلاغ</button>
          <a href="{{ $backUrl }}" class="btn btn--soft">إلغاء</a>
        </div>
      </form>
    </div>
  </main>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/report.js') }}"></script>
@endpush
