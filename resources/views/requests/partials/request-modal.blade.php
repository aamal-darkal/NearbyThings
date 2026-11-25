{{--
  نافذة إرسال طلب إلى المالك (بصفحة تفاصيل الغرض)
  الحقول بتتغير حسب نوع العرض: تأجير (فترة التأجير) / تبادل (المقابل)
  المتغيرات: $item
--}}
@php
  // أغراضي النشطة (للتبادل)
  $myItems = $item->transaction_type === 'exchange'
      ? auth()->user()->items()->where('state', 'active')->orderBy('title')->get()
      : collect();
  $hasErrors = $errors->hasAny(['requester_note', 'count', 'start_date', 'end_date', 'offered_item_id', 'offered_description']);
@endphp

<div class="modal" id="request-modal" role="dialog" aria-modal="true" aria-labelledby="request-title" @if ($hasErrors) data-open="true" @endif>
  <div class="modal__dialog">
    <div class="modal__header">
      <span class="modal__header-icon"><x-icon name="send" /></span>
      <div>
        <h2 id="request-title">إرسال طلب إلى المالك</h2>
        <p>سيصل طلبك للمالك ليقبله أو يرفضه</p>
      </div>
      <button class="modal__close js-close-modal" type="button" aria-label="إغلاق"><x-icon name="x" /></button>
    </div>

    <form method="POST" action="{{ route('requests.store', $item) }}" id="request-form">
      @csrf
      <div class="modal__body">
        <div class="request-summary">
          @if ($item->mainImageUrl())
            <img src="{{ $item->mainImageUrl() }}" alt="">
          @endif
          <div>
            <span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span>
            <span class="price">{{ $item->priceLabel() }}</span>
            <h3>{{ $item->title }}</h3>
            <p>المالك: {{ $item->owner->shortName() }} • {{ $item->areaLabel() }}</p>
          </div>
        </div>

        {{-- التأجير: فترة التأجير والتكلفة التقديرية --}}
        @if ($item->transaction_type === 'rent')
          <div class="form-grid-2 rent-dates" data-price="{{ $item->price }}" data-unit="{{ $item->rent_unit }}">
            <div class="form-group @error('start_date') has-error @enderror">
              <label class="form-label" for="request-start">من تاريخ <span class="required">*</span></label>
              <input class="input" type="date" id="request-start" name="start_date" min="{{ today()->toDateString() }}" value="{{ old('start_date', today()->toDateString()) }}">
              <p class="form-error">@error('start_date'){{ $message }}@enderror</p>
            </div>
            <div class="form-group @error('end_date') has-error @enderror">
              <label class="form-label" for="request-end">إلى تاريخ <span class="required">*</span></label>
              <input class="input" type="date" id="request-end" name="end_date" min="{{ today()->toDateString() }}" value="{{ old('end_date', today()->addDay()->toDateString()) }}">
              <p class="form-error">@error('end_date'){{ $message }}@enderror</p>
            </div>
          </div>
          <p class="form-note rent-total"><x-icon name="calendar" /><span id="rent-total">المدة والتكلفة تظهر هنا</span></p>
          <p class="form-note"><x-icon name="info" />أقصى مدة يسمح بها المالك: {{ $item->max_rent_duration }} {{ $item->rentUnitLabel() }}</p>
        @endif

        {{-- التبادل: غرض من أغراضي أو وصف --}}
        @if ($item->transaction_type === 'exchange')
          @if (! empty($item->variants['exchange_for']))
            <p class="form-note"><x-icon name="repeat" />يرغب المالك بـ: {{ $item->variants['exchange_for'] }}</p>
          @endif
          <div class="form-group @error('offered_item_id') has-error @enderror">
            <label class="form-label" for="request-offer-item">اختر غرضاً من أغراضك للمبادلة</label>
            <select class="select" id="request-offer-item" name="offered_item_id">
              <option value="">— بدون، سأكتب وصفاً —</option>
              @foreach ($myItems as $myItem)
                <option value="{{ $myItem->id }}" @selected(old('offered_item_id') == $myItem->id)>{{ $myItem->title }}</option>
              @endforeach
            </select>
            <p class="form-error">@error('offered_item_id'){{ $message }}@enderror</p>
          </div>
          <div class="form-group @error('offered_description') has-error @enderror">
            <label class="form-label" for="request-offer-text">أو صف ما تعرضه مقابله</label>
            <textarea class="textarea" id="request-offer-text" name="offered_description" maxlength="500" placeholder="مثال: طقم مفاتيح عدة يدوية 12 قطعة">{{ old('offered_description') }}</textarea>
            <p class="form-error">@error('offered_description'){{ $message }}@enderror</p>
          </div>
        @endif

        @if ($item->count > 1)
          <div class="form-group form-group--small @error('count') has-error @enderror">
            <label class="form-label" for="request-count">الكمية</label>
            <input class="input" type="number" id="request-count" name="count" min="1" max="{{ $item->count }}" value="{{ old('count', 1) }}">
            <p class="form-error">@error('count'){{ $message }}@enderror</p>
          </div>
        @endif

        <div class="form-group @error('requester_note') has-error @enderror">
          <div class="form-label-row">
            <label class="form-label" for="request-message">رسالة أو ملاحظة للمالك (اختياري)</label>
            <span class="muted">الحد الأقصى 250 حرف</span>
          </div>
          <textarea class="textarea" id="request-message" name="requester_note" maxlength="250" placeholder="مثال: متى يناسبك التسليم؟">{{ old('requester_note') }}</textarea>
          <p class="form-error">@error('requester_note'){{ $message }}@enderror</p>
          <p class="form-note"><x-icon name="bell" />سيصلك إشعار عند رد المالك على طلبك</p>
        </div>

        <div class="privacy-box">
          <x-icon name="lock" />
          <div>
            <strong>ملاحظة هامة لحماية الخصوصية:</strong>
            حفاظاً على الخصوصية، تظهر بيانات التواصل المباشر (رقم الهاتف وموقع اللقاء الدقيق) فور قبول المالك لطلبك واعتماد الموعد.
          </div>
        </div>
      </div>

      <div class="modal__footer">
        <button type="submit" class="btn btn--primary btn--lg"><x-icon name="check-circle" />تأكيد وإرسال الطلب</button>
        <button type="button" class="btn btn--soft btn--lg js-close-modal">إلغاء</button>
      </div>
    </form>
  </div>
</div>
