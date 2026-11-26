{{-- لوحة تحكم المشرف --}}
@extends('layouts.admin')

@section('title', 'لوحة التحكم')

@php
  // ===== نقاط المخطط الخطي (الإعلانات الجديدة آخر 30 يوم) =====
  $chartWidth = 600;
  $chartHeight = 200;
  $max = max(max($daily), 1);
  $points = [];
  foreach ($daily as $i => $count) {
      $x = round($i * $chartWidth / (count($daily) - 1), 1);
      $y = round($chartHeight - ($count / $max) * ($chartHeight - 20), 1);
      $points[] = $x.','.$y;
  }
  $line = implode(' ', $points);
  $area = '0,'.$chartHeight.' '.$line.' '.$chartWidth.','.$chartHeight;

  // ===== المخطط الدائري (نسبة كل نوع) =====
  $circumference = 2 * M_PI * 60;
  $colors = ['sale' => '#1b7a6e', 'rent' => '#4b5350', 'exchange' => '#7fd6c4'];
  $offset = 0;
@endphp

@section('content')
  <div class="page-head">
    <div>
      <h1>لوحة التحكم</h1>
      <p>نظرة عامة على نشاط المنصة، البلاغات المعلقة والطلبات الجارية</p>
    </div>
  </div>

  <!-- بطاقات الأرقام -->
  <section class="stat-cards" aria-label="المؤشرات">
    <div class="stat-card stat-card--column">
      <div class="stat-card__row"><span class="stat-card__label">إجمالي الإعلانات</span><span class="stat-card__icon"><x-icon name="package" /></span></div>
      <strong class="stat-card__value">{{ $stats['items'] }} <small>إعلاناً</small></strong>
      <span class="stat-card__trend">+{{ $stats['items_week'] }} هذا الأسبوع</span>
    </div>
    <div class="stat-card stat-card--column">
      <div class="stat-card__row"><span class="stat-card__label">الطلبات النشطة</span><span class="stat-card__icon"><x-icon name="handshake" /></span></div>
      <strong class="stat-card__value">{{ $stats['active_requests'] }} <small>طلباً جارياً</small></strong>
      <span class="stat-card__trend stat-card__trend--muted">قيد التفاعل حالياً</span>
    </div>
    <div class="stat-card stat-card--column">
      <div class="stat-card__row"><span class="stat-card__label">البلاغات المفتوحة</span><span class="stat-card__icon stat-card__icon--danger"><x-icon name="flag" /></span></div>
      <strong class="stat-card__value">{{ $stats['open_reports'] }} <small>بلاغات</small></strong>
      <span class="stat-card__trend stat-card__trend--danger">تتطلب مراجعة</span>
    </div>
    <div class="stat-card stat-card--column">
      <div class="stat-card__row"><span class="stat-card__label">إجمالي المستخدمين</span><span class="stat-card__icon"><x-icon name="users" /></span></div>
      <strong class="stat-card__value">{{ $stats['users'] }} <small>مستخدماً</small></strong>
      <span class="stat-card__trend">+{{ $stats['users_month'] }} هذا الشهر</span>
    </div>
  </section>

  <div class="dashboard-grid">
    <!-- المخطط الخطي -->
    <section class="panel chart-panel">
      <div class="chart-panel__head">
        <div>
          <h2 class="panel__title">الإعلانات الجديدة خلال آخر 30 يوماً</h2>
          <p class="panel__subtitle">معدل الإدراج اليومي للسلع المعروضة للبيع، التبادل، والتأجير</p>
        </div>
        <span class="legend-dot">إعلانات مضافة</span>
      </div>

      <svg class="line-chart" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" preserveAspectRatio="none" role="img" aria-label="عدد الإعلانات الجديدة لكل يوم">
        <line class="line-chart__grid" x1="0" x2="{{ $chartWidth }}" y1="50" y2="50" />
        <line class="line-chart__grid" x1="0" x2="{{ $chartWidth }}" y1="100" y2="100" />
        <line class="line-chart__grid" x1="0" x2="{{ $chartWidth }}" y1="150" y2="150" />
        <polygon class="line-chart__area" points="{{ $area }}" />
        <polyline class="line-chart__line" points="{{ $line }}" />
      </svg>
      <div class="chart-labels">
        <span>قبل 30 يوماً</span><span>قبل 20 يوماً</span><span>قبل 10 أيام</span><span>اليوم</span>
      </div>
    </section>

    <!-- المخطط الدائري -->
    <section class="panel chart-panel">
      <h2 class="panel__title">العمليات حسب النوع</h2>
      <p class="panel__subtitle">توزيع العروض والصفقات المقامة</p>

      <div class="donut">
        <svg viewBox="0 0 160 160" width="160" height="160" aria-hidden="true">
          <circle cx="80" cy="80" r="60" fill="none" stroke="#f0ece8" stroke-width="22" />
          @foreach ($types as $key => $type)
            @php $length = $circumference * $type['count'] / max($stats['items'], 1); @endphp
            <circle cx="80" cy="80" r="60" fill="none" stroke="{{ $colors[$key] }}" stroke-width="22"
                    stroke-dasharray="{{ $length }} {{ $circumference - $length }}" stroke-dashoffset="{{ -$offset }}" />
            @php $offset += $length; @endphp
          @endforeach
        </svg>
        <div class="donut__center"><strong>{{ $stats['items'] }}</strong>إجمالي الإعلانات</div>
      </div>

      <ul class="type-split">
        @foreach ($types as $key => $type)
          <li class="type-split__{{ $key }}">{{ $type['label'] }} <span class="muted">{{ $type['count'] }} إعلان</span><strong>{{ $type['percent'] }}%</strong></li>
        @endforeach
      </ul>
    </section>
  </div>

  <div class="dashboard-grid">
    <!-- أحدث البلاغات -->
    <section class="panel chart-panel">
      <div class="chart-panel__head">
        <div>
          <h2 class="panel__title">أحدث البلاغات <span class="status status--danger">{{ $stats['open_reports'] }} بلاغات غير محسومة</span></h2>
          <p class="panel__subtitle">بلاغات المستخدمين بانتظار المراجعة والتدقيق الإداري</p>
        </div>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>المُبلِّغ</th><th>الغرض المُبلَّغ عنه</th><th>السبب</th><th>التاريخ</th></tr>
          </thead>
          <tbody>
            @forelse ($latestReports as $report)
              <tr>
                <td><strong>{{ $report->reporter_name }}</strong><span class="data-table__muted">{{ $report->reporter_area }}</span></td>
                <td>{{ $report->item_title ?? 'غرض محذوف' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($report->reason, 50) }}</td>
                <td class="muted">{{ \App\Support\Dates::ago(\Illuminate\Support\Carbon::parse($report->reported_at)) }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="muted">لا توجد بلاغات مفتوحة حالياً.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <!-- اقتراحات التصنيفات -->
    <section class="panel chart-panel">
      <h2 class="panel__title">اقتراحات التصنيفات</h2>
      <p class="panel__subtitle">بانتظار المراجعة والاعتماد</p>

      <div class="suggest-total">
        <strong>{{ $suggestions->count() }}</strong>
        <span>اقتراحات جديدة بانتظار الاعتماد</span>
      </div>

      @if ($suggestions->isNotEmpty())
        <p class="muted">أحدث الاقتراحات:</p>
        <ul class="mini-list">
          @foreach ($suggestions->take(3) as $suggestion)
            <li>
              <span class="mini-list__icon">{{ $suggestion->iconLabel() }}</span>
              <div>
                <strong>{{ $suggestion->category_name }}</strong>
                <span class="muted">{{ $suggestion->items_count }} إعلان • {{ \App\Support\Dates::ago($suggestion->created_at) }}</span>
              </div>
            </li>
          @endforeach
        </ul>
      @endif

      <a href="{{ route('admin.suggestions.index') }}" class="btn btn--light btn--block">عرض كل الاقتراحات <x-icon name="arrow-left" /></a>
    </section>
  </div>
@endsection
