{{-- البلاغات (المشرف) --}}
@extends('layouts.admin')

@section('title', 'البلاغات')

@section('content')
  <div class="page-head">
    <div>
      <h1>البلاغات</h1>
      <p>بلاغات المستخدمين عن المخالفات والاعتراضات على الإرجاع، بانتظار المراجعة واتخاذ الإجراء المناسب</p>
    </div>
    <span class="status status--danger">{{ $pendingCount }} بلاغات بانتظار المراجعة</span>
  </div>

  <form class="panel filters-bar" method="GET" action="{{ route('admin.reports.index') }}">
    <label class="sr-only" for="report-status">الحالة</label>
    <select class="select js-auto-submit" id="report-status" name="status">
      <option value="">الحالة: الكل</option>
      <option value="pending" @selected(request('status') === 'pending')>جديد</option>
      <option value="resolved" @selected(request('status') === 'resolved')>تم الحل</option>
      <option value="rejected" @selected(request('status') === 'rejected')>مرفوض</option>
    </select>
    <label class="sr-only" for="report-category">سبب البلاغ</label>
    <select class="select js-auto-submit" id="report-category" name="category">
      <option value="">سبب البلاغ: الكل</option>
      @foreach ($categories as $key => $label)
        <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
      @endforeach
    </select>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>المُبلَّغ عنه</th><th>المُبلِّغ</th><th>السبب</th><th>التاريخ</th><th>الحالة</th><th>إجراء</th></tr>
      </thead>
      <tbody>
        @forelse ($reports as $report)
          @php $relatedItem = $report->relatedItem(); @endphp
          <tr>
            <td>
              <strong>{{ $relatedItem?->title ?? 'غرض محذوف' }}</strong>
              <span class="data-table__muted">{{ $report->request_id ? 'بلاغ على عملية' : 'بلاغ على إعلان' }} • {{ $report->reportedUser()?->full_name }}</span>
            </td>
            <td>
              <strong>{{ $report->reporter->full_name }}</strong>
              <span class="data-table__muted">{{ $report->reporter->address }}</span>
            </td>
            <td><span @class(['status', 'status--danger' => $report->category === 'fraud', 'status--warning' => $report->category === 'prohibited'])>{{ $report->categoryLabel() }}</span></td>
            <td class="muted">{{ \App\Support\Dates::ago($report->reported_at) }}</td>
            <td><span class="status {{ $report->statusClass() }}">{{ $report->statusLabel() }}</span></td>
            <td><a href="{{ request()->fullUrlWithQuery(['report' => $report->id]) }}" class="btn btn--light btn--xs"><x-icon name="eye" />مراجعة</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">لا توجد بلاغات مطابقة.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @include('partials.pagination', ['paginator' => $reports, 'label' => 'بلاغ'])
@endsection

@push('modals')
  {{-- تفاصيل البلاغ (لوحة جانبية) --}}
  @if ($selected)
    @php
      $relatedItem = $selected->relatedItem();
      $reportedUser = $selected->reportedUser();
    @endphp
    <a href="{{ request()->fullUrlWithoutQuery(['report']) }}" class="drawer-backdrop" aria-label="إغلاق"></a>
    <aside class="drawer" role="dialog" aria-modal="true" aria-labelledby="report-title">
      <div class="drawer__head">
        <h2 id="report-title">
          <x-icon name="flag" />تفاصيل البلاغ
          <span class="status {{ $selected->statusClass() }}">{{ $selected->statusLabel() }}</span>
        </h2>
        <a href="{{ request()->fullUrlWithoutQuery(['report']) }}" class="icon-btn" aria-label="إغلاق"><x-icon name="x" /></a>
      </div>

      <div class="drawer__body">
        <p class="muted">{{ \App\Support\Dates::dateTime($selected->reported_at) }}</p>

        <section class="move-summary">
          <span class="muted">المُبلِّغ (صاحب الشكوى)</span>
          <div class="user-cell">
            <span class="avatar">{{ $selected->reporter->initial() }}</span>
            <div><strong>{{ $selected->reporter->full_name }}</strong><span class="muted">{{ $selected->reporter->areaLabel() }}</span></div>
          </div>
        </section>

        <section class="move-summary">
          <span class="muted">الطرف المُبلَّغ عنه والغرض المرتبط</span>
          @if ($relatedItem)
            <p><a href="{{ route('items.show', $relatedItem) }}"><strong>{{ $relatedItem->title }}</strong></a></p>
            <p>المالك: {{ $relatedItem->owner->full_name }} • <span class="badge badge--{{ $relatedItem->transaction_type }}">{{ $relatedItem->typeLabel() }}</span> {{ $relatedItem->priceLabel() }}</p>
          @endif
          @if ($reportedUser)
            <p>المُبلَّغ عنه: <strong>{{ $reportedUser->full_name }}</strong> @if ($reportedUser->is_blocked)<span class="status status--danger">محظور</span>@endif</p>
          @endif
        </section>

        <section class="report-mini">
          <div class="report-mini__head">
            <strong>سبب البلاغ والتفاصيل</strong>
            <span class="status status--danger">{{ $selected->categoryLabel() }}</span>
          </div>
          <p>"{{ $selected->reason }}"</p>
        </section>

        @if ($selected->request)
          <section class="move-summary">
            <span class="muted">ملخص العملية المرتبطة</span>
            <p>نوع التعامل: <strong>{{ $selected->request->item->typeLabel() }}</strong> • الحالة: <strong>{{ $selected->request->statusLabel() }}</strong></p>
            @if ($selected->request->rental)
              <p>فترة الحجز: من {{ \App\Support\Dates::full($selected->request->rental->start_date) }} إلى {{ \App\Support\Dates::full($selected->request->rental->expected_return_date) }}</p>
              <p>حالة الإرجاع: {{ $selected->request->rental->returnStatusLabel() }}</p>
            @endif
          </section>
        @endif

        @if ($selected->status === 'pending')
          <form method="POST" action="{{ route('admin.reports.resolve', $selected) }}" class="drawer-section">
            @csrf
            @method('PATCH')
            <div class="form-group">
              <label class="form-label" for="admin-note">ملاحظة المشرف (سجل داخلي):</label>
              <textarea class="textarea" id="admin-note" name="admin_note" maxlength="500" placeholder="اكتب ملاحظة المشرف أو سبب الإجراء المتخذ هنا...">{{ old('admin_note') }}</textarea>
            </div>
            <div class="report-actions">
              @if ($reportedUser && ! $reportedUser->is_admin && ! $reportedUser->is_blocked)
                <button type="submit" name="action" value="ban_user" class="btn btn--danger btn--block"><x-icon name="ban" />حظر المستخدم ({{ $reportedUser->full_name }})</button>
              @endif
              @if ($relatedItem && $relatedItem->state !== 'banned')
                <button type="submit" name="action" value="block_item" class="btn btn--light btn--block btn--danger-text"><x-icon name="eye-off" />حجب هذا الغرض من المنصة</button>
              @endif
              <button type="submit" name="action" value="reject" class="btn btn--soft btn--block"><x-icon name="x" />رفض البلاغ وحفظ السجل</button>
            </div>
          </form>
        @else
          <section class="move-summary">
            <span class="muted">ملاحظة المشرف</span>
            <p>{{ $selected->admin_note ?: '—' }}</p>
          </section>
        @endif
      </div>
    </aside>
  @endif
@endpush
