{{-- المستخدمون (المشرف) --}}
@extends('layouts.admin')

@section('title', 'المستخدمون')

@section('content')
  <div class="page-head">
    <div>
      <h1>المستخدمون</h1>
      <p>إدارة حسابات المستخدمين ومتابعة تقييماتهم والبلاغات المسجلة ضدهم</p>
    </div>
  </div>

  <form class="panel filters-bar" method="GET" action="{{ route('admin.users.index') }}">
    <label class="sr-only" for="user-search">بحث</label>
    <div class="input-wrap">
      <x-icon name="search" />
      <input type="search" id="user-search" name="q" value="{{ request('q') }}" placeholder="ابحث بالاسم، البريد الإلكتروني، أو المنطقة...">
    </div>
    <label class="sr-only" for="user-status">الحالة</label>
    <select class="select js-auto-submit" id="user-status" name="status">
      <option value="">الحالة: الكل</option>
      <option value="active" @selected(request('status') === 'active')>نشط</option>
      <option value="blocked" @selected(request('status') === 'blocked')>محظور</option>
    </select>
    <button type="submit" class="btn btn--primary btn--sm"><x-icon name="search" />بحث</button>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>المستخدم</th><th>البريد الإلكتروني</th><th>المنطقة</th><th>التقييم</th><th>الإعلانات</th><th>البلاغات</th><th>الحالة</th><th>إجراء</th></tr>
      </thead>
      <tbody>
        @foreach ($users as $user)
          <tr>
            <td>
              <div class="user-cell">
                <span class="avatar">{{ $user->initial() }}</span>
                <div>
                  <strong>{{ $user->full_name }}</strong>
                  @if ($user->is_admin)<span class="admin-badge">مشرف</span>@endif
                </div>
              </div>
            </td>
            <td class="muted">{{ $user->email }}</td>
            <td>{{ $user->address ?: '—' }}</td>
            <td><span class="rating-cell"><x-icon name="star" />{{ number_format($user->rate, 1) }}</span></td>
            <td>{{ $user->active_items_count }} إعلان</td>
            <td>
              @if ($user->reports_count > 0)
                <span class="status status--danger">{{ $user->reports_count }} بلاغات</span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>
              <span @class(['status', 'status--success' => ! $user->is_blocked, 'status--danger' => $user->is_blocked])>
                {{ $user->is_blocked ? 'محظور' : 'نشط' }}
              </span>
            </td>
            <td><a href="{{ request()->fullUrlWithQuery(['user' => $user->id]) }}" class="link">عرض</a></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @include('partials.pagination', ['paginator' => $users, 'label' => 'مستخدم'])
@endsection

@push('modals')
  {{-- ملف المستخدم (لوحة جانبية تفتح عند اختيار مستخدم) --}}
  @if ($selected)
    <a href="{{ request()->fullUrlWithoutQuery(['user']) }}" class="drawer-backdrop" aria-label="إغلاق"></a>
    <aside class="drawer" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
      <div class="drawer__head">
        <h2 id="drawer-title">
          ملف المستخدم
          <span @class(['status', 'status--success' => ! $selected->is_blocked, 'status--danger' => $selected->is_blocked])>{{ $selected->is_blocked ? 'محظور' : 'نشط' }}</span>
        </h2>
        <a href="{{ request()->fullUrlWithoutQuery(['user']) }}" class="icon-btn" aria-label="إغلاق"><x-icon name="x" /></a>
      </div>

      <div class="drawer__body">
        <section class="drawer-profile">
          <span class="avatar">{{ $selected->initial() }}</span>
          <h3>{{ $selected->full_name }}</h3>
          <p>{{ $selected->email }}</p>
          <p><x-icon name="map-pin" /> المنطقة: {{ $selected->areaLabel() ?: '—' }}</p>

          <div class="drawer-stats">
            <div><span>التقييم العام</span><strong>{{ number_format($selected->rate, 1) }} ★</strong></div>
            <div><span>العمليات المكتملة</span><strong>{{ $completedDeals }} عملية ناجحة</strong></div>
            <div><span>عضو منذ</span><strong>{{ \App\Support\Dates::monthYear($selected->created_at) }}</strong></div>
            <div><span>الإعلانات النشطة</span><strong>{{ $selected->active_items_count }} إعلانات</strong></div>
          </div>
        </section>

        <section class="drawer-section">
          <h3><x-icon name="flag" />أحدث البلاغات المسجلة ضده ({{ $selectedReports->count() }} بلاغ)</h3>
          @forelse ($selectedReports as $report)
            <div class="report-mini">
              <div class="report-mini__head">
                <strong>مُبلِّغ: {{ $report->reporter_name }}</strong>
                <span @class(['status', 'status--warning' => $report->status === 'pending', 'status--success' => $report->status === 'resolved'])>
                  {{ ['pending' => 'قيد المراجعة', 'resolved' => 'تم الحل', 'rejected' => 'مرفوض'][$report->status] ?? $report->status }}
                </span>
              </div>
              <p>"{{ $report->reason }}"</p>
              <span class="muted">{{ \App\Support\Dates::ago(\Illuminate\Support\Carbon::parse($report->reported_at)) }}</span>
            </div>
          @empty
            <p class="muted">لا توجد بلاغات مسجلة ضد هذا المستخدم.</p>
          @endforelse
        </section>

        @unless ($selected->is_admin)
          <section class="ban-box drawer-section">
            @if ($selected->is_blocked)
              <h3><x-icon name="refresh" />رفع الحظر</h3>
              <p class="muted">سيتمكن المستخدم من الدخول لحسابه وستعود إعلاناته للظهور.</p>
              <form method="POST" action="{{ route('admin.users.unban', $selected) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn--primary btn--block">رفع الحظر عن الحساب</button>
              </form>
            @else
              <h3><x-icon name="ban" />الإجراء الإداري الرادع</h3>
              <form method="POST" action="{{ route('admin.users.ban', $selected) }}" class="js-confirm" data-confirm="هل أنت متأكد من حظر هذا المستخدم؟">
                @csrf
                @method('PATCH')
                <div class="form-group @error('reason') has-error @enderror">
                  <label class="form-label" for="ban-reason">سبب الحظر <span class="required">*</span></label>
                  <textarea class="textarea" id="ban-reason" name="reason" maxlength="500" placeholder="اكتب سبب الحظر بالتفصيل (مطلوب قبل التأكيد)...">{{ old('reason') }}</textarea>
                  <p class="form-error">@error('reason'){{ $message }}@enderror</p>
                </div>
                <p class="form-note"><x-icon name="info" />سيتم إيقاف حساب المستخدم فوراً وحجب كافة إعلاناته من المنصة مع إرسال إشعار رسمي له.</p>
                <button type="submit" class="btn btn--danger btn--block"><x-icon name="ban" />حظر المستخدم</button>
              </form>
            @endif
          </section>
        @endunless
      </div>
    </aside>
  @endif
@endpush
