{{--
  سطر فترة التأجير: "من 25 إلى 27 تشرين الثاني • 3 أيام • 900 ل.س"
  المتغيرات: $deal (الطلب)
--}}
@php
  $rental = $deal->rental;
  $item = $deal->item;
  $days = $rental->days();
  $units = (int) ceil($days / ['day' => 1, 'month' => 30, 'year' => 365][$item->rent_unit ?? 'day']);
  $total = $units * $item->price;
@endphp
<span class="request-line">
  <x-icon name="calendar" />
  من {{ \App\Support\Dates::full($rental->start_date) }} إلى {{ \App\Support\Dates::full($rental->expected_return_date) }}
  • {{ \App\Support\Dates::count($days, 'يوم', 'يومين', 'أيام') }}
  • <strong>{{ number_format($total) }} ل.س</strong>
</span>
