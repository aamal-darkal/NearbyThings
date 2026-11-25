{{-- الإعلانات والطلبات (المشرف) --}}
@extends('layouts.admin')

@section('title', 'الإعلانات والطلبات')

@php
  $itemStates = ['active' => 'نشط', 'inactive' => 'موقوف مؤقتاً', 'banned' => 'محجوب', 'sold' => 'مباع', 'rented' => 'مؤجر'];
  $requestStates = ['waiting' => 'قيد الانتظار', 'approved' => 'مقبول', 'rejected' => 'مرفوض', 'canceled' => 'ملغى', 'received' => 'مستلم', 'finished' => 'مكتمل'];
  $requestStatusClass = ['waiting' => 'status--warning', 'approved' => 'status--success', 'rejected' => 'status--danger', 'canceled' => '', 'received' => 'status--info', 'finished' => 'status--success'];
  $itemStatusClass = ['active' => 'status--success', 'inactive' => '', 'banned' => 'status--danger', 'sold' => 'status--info', 'rented' => 'status--info'];
  $typeNames = ['sale' => 'بيع', 'exchange' => 'تبادل', 'rent' => 'تأجير'];
@endphp

@section('content')
  <div class="page-head">
    <div>
      <h1>الإعلانات والطلبات</h1>
      <p>متابعة الإعلانات المنشورة والطلبات بين المستخدمين</p>
    </div>
    <nav class="segmented" aria-label="نوع السجلات">
      <a href="{{ route('admin.listings.index') }}" @class(['is-active' => $tab === 'items'])>الإعلانات <span class="chip__count">{{ $itemsCount }}</span></a>
      <a href="{{ route('admin.listings.index', ['tab' => 'requests']) }}" @class(['is-active' => $tab === 'requests'])>الطلبات <span class="chip__count">{{ $requestsCount }}</span></a>
    </nav>
  </div>

  <!-- البحث والفلاتر -->
  <form class="panel filters-bar" method="GET" action="{{ route('admin.listings.index') }}">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <label class="sr-only" for="listing-search">بحث</label>
    <div class="input-wrap">
      <x-icon name="search" />
      <input type="search" id="listing-search" name="q" value="{{ request('q') }}"
             placeholder="{{ $tab === 'items' ? 'بحث باسم الغرض أو المالك...' : 'بحث باسم الغرض، صاحب الطلب، أو المالك...' }}">
    </div>
    <label class="sr-only" for="listing-type">نوع التعامل</label>
    <select class="select js-auto-submit" id="listing-type" name="type">
      <option value="">نوع التعامل: الكل</option>
      @foreach ($typeNames as $key => $label)
        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
      @endforeach
    </select>
    <label class="sr-only" for="listing-status">الحالة</label>
    <select class="select js-auto-submit" id="listing-status" name="status">
      <option value="">الحالة: الكل</option>
      @foreach ($tab === 'items' ? $itemStates : $requestStates as $key => $label)
        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn btn--primary btn--sm"><x-icon name="search" />بحث</button>
  </form>

  <div class="table-wrap">
    @if ($tab === 'items')
      <table class="data-table">
        <thead>
          <tr><th>الغرض</th><th>المالك</th><th>نوع التعامل</th><th>السعر</th><th>الحالة</th><th>التاريخ</th><th>إجراء</th></tr>
        </thead>
        <tbody>
          @forelse ($rows as $item)
            <tr>
              <td>
                <a href="{{ route('items.show', $item) }}" class="thumb-cell">
                  @if ($item->mainImageUrl())
                    <img src="{{ $item->mainImageUrl() }}" alt="">
                  @else
                    <span class="thumb-cell__empty"></span>
                  @endif
                  {{ $item->title }}
                </a>
              </td>
              <td>{{ $item->owner->full_name }}</td>
              <td><span class="badge badge--{{ $item->transaction_type }}">{{ $item->typeLabel() }}</span></td>
              <td>{{ $item->priceLabel() }}</td>
              <td><span class="status {{ $itemStatusClass[$item->state] }}">{{ $itemStates[$item->state] }}</span></td>
              <td class="muted">{{ \App\Support\Dates::ago($item->created_at) }}</td>
              <td>
                @if ($item->state === 'banned')
                  <form method="POST" action="{{ route('admin.items.unblock', $item) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn--light btn--xs">إلغاء الحجب</button>
                  </form>
                @else
                  <form method="POST" action="{{ route('admin.items.block', $item) }}" class="js-confirm" data-confirm="هل تريد حجب هذا الإعلان؟">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn--text-danger btn--xs"><x-icon name="ban" />حجب</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="muted">لا توجد إعلانات مطابقة.</td></tr>
          @endforelse
        </tbody>
      </table>
    @else
      <table class="data-table">
        <thead>
          <tr><th>الغرض</th><th>صاحب الطلب</th><th>المالك</th><th>نوع التعامل</th><th>التفاصيل</th><th>الحالة</th><th>التاريخ</th></tr>
        </thead>
        <tbody>
          @forelse ($rows as $row)
            <tr>
              <td><a href="{{ route('items.show', $row->item_id) }}"><strong>{{ $row->title }}</strong></a></td>
              <td><strong>{{ $row->requester_name }}</strong></td>
              <td>{{ $row->owner_name }}</td>
              <td><span class="badge badge--{{ $row->transaction_type }}">{{ $typeNames[$row->transaction_type] }}</span></td>
              <td>
                @if ($row->transaction_type === 'rent' && $row->start_date)
                  من {{ \App\Support\Dates::full(\Illuminate\Support\Carbon::parse($row->start_date)) }} إلى {{ \App\Support\Dates::full(\Illuminate\Support\Carbon::parse($row->expected_return_date)) }}
                @elseif ($row->transaction_type === 'exchange')
                  بدل: {{ \Illuminate\Support\Str::limit($row->offer ?? '—', 40) }}
                @else
                  <strong class="my-price">{{ number_format($row->price) }} ل.س</strong>
                @endif
              </td>
              <td><span class="status {{ $requestStatusClass[$row->status] }}">{{ $requestStates[$row->status] }}</span></td>
              <td class="muted">{{ \App\Support\Dates::ago(\Illuminate\Support\Carbon::parse($row->request_date)) }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="muted">لا توجد طلبات مطابقة.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif
  </div>

  @include('partials.pagination', ['paginator' => $rows, 'label' => $tab === 'items' ? 'إعلاناً' : 'طلباً'])
@endsection
