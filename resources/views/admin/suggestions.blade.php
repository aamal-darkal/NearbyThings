{{-- اقتراحات التصنيفات (المشرف) --}}
@extends('layouts.admin')

@section('title', 'اقتراحات التصنيفات')

@section('content')
  <div class="page-head">
    <div>
      <h1>اقتراحات التصنيفات</h1>
      <p>تصنيفات اقترحها المستخدمون عند إضافة إعلاناتهم، بانتظار الاعتماد أو إعادة التوجيه</p>
    </div>
    <div class="chips">
      <span class="tag tag--accent"><x-icon name="lightbulb" />{{ $suggestions->count() }} بانتظار المراجعة</span>
      <span class="tag"><x-icon name="check-circle" />{{ $approvedCount }} تصنيف معتمد هذا الشهر</span>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>التصنيف المقترح</th><th>الإعلانات المرتبطة</th><th>المقترح</th><th>التاريخ</th><th>إجراء</th></tr>
      </thead>
      <tbody>
        @forelse ($suggestions as $suggestion)
          @php $firstItem = $suggestion->items->first(); @endphp
          <tr>
            <td><strong>{{ $suggestion->category_name }}</strong></td>
            <td>
              @forelse ($suggestion->items->take(2) as $item)
                <a href="{{ route('items.show', $item) }}" class="data-table__muted">{{ $item->title }}</a>
              @empty
                <span class="muted">لا توجد إعلانات</span>
              @endforelse
              @if ($suggestion->items->count() > 2)
                <span class="data-table__muted">و{{ $suggestion->items->count() - 2 }} أخرى</span>
              @endif
            </td>
            <td>{{ $firstItem?->owner->full_name ?? '—' }}</td>
            <td class="muted">{{ \App\Support\Dates::ago($suggestion->created_at) }}</td>
            <td>
              <div class="suggestion-row__actions">
                <form method="POST" action="{{ route('admin.suggestions.approve', $suggestion) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn--primary btn--xs"><x-icon name="check" />اعتماد</button>
                </form>
                <button type="button" class="btn btn--light btn--xs js-open-modal" data-modal="move-modal"
                        data-action="{{ route('admin.suggestions.move', $suggestion) }}" data-method="PATCH"
                        data-text-category="{{ $suggestion->category_name }}"
                        data-text-item="{{ $firstItem?->title ?? '—' }}"
                        data-text-owner="{{ $firstItem?->owner->full_name ?? '—' }}">
                  <x-icon name="folder" />نقل إلى تصنيف موجود
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="muted">لا توجد اقتراحات جديدة حالياً.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection

@push('modals')
  <div class="modal" id="move-modal" role="dialog" aria-modal="true" aria-labelledby="move-title" @error('target_id') data-open="true" @enderror>
    <div class="modal__dialog">
      <form method="POST" action="">
        @csrf
        <input type="hidden" name="_method" value="PATCH">
        <div class="modal__header">
          <span class="modal__header-icon"><x-icon name="folder" /></span>
          <div>
            <h2 id="move-title">نقل إلى تصنيف موجود</h2>
            <p>إعادة تعيين الإعلان لقسم قائم في المنصة</p>
          </div>
          <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
        </div>
        <div class="modal__body">
          <div class="move-summary">
            <span class="muted">التصنيف المقترح:</span>
            <strong class="js-text-category"></strong>
            <p><span class="muted">الإعلان:</span> <span class="js-text-item"></span> (المقترح: <span class="js-text-owner"></span>)</p>
          </div>

          <div class="form-group @error('target_id') has-error @enderror">
            <label class="form-label" for="move-target">اختر التصنيف <span class="required">*</span></label>
            <select class="select" id="move-target" name="target_id">
              <option value="">اختر التصنيف</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->iconLabel() }} {{ $category->category_name }}</option>
              @endforeach
            </select>
            <p class="form-error">@error('target_id'){{ $message }}@enderror</p>
          </div>

          <div class="alert">
            <x-icon name="info" />
            <div>سيُنقل الإعلان إلى التصنيف المختار ويُحذف الاقتراح، ويصل إشعار للمستخدم.</div>
          </div>
        </div>
        <div class="modal__footer">
          <button type="submit" class="btn btn--primary">تأكيد</button>
          <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
@endpush
