{{-- التصنيفات وحالات الغرض (المشرف) --}}
@extends('layouts.admin')

@section('title', 'التصنيفات وحالات الغرض')

@php
  // رابط النموذج بعد رجوع أخطاء التحقق (منقبله بس اذا كان من روابط لوحة المشرف)
  $oldAction = str_starts_with((string) old('_action'), url('/admin/')) ? old('_action') : null;
@endphp

@section('content')
  <div class="page-head">
    <div>
      <h1>التصنيفات وحالات الغرض</h1>
      <p>إدارة تصنيفات الأغراض وحالات المعروضات المعتمدة عبر المنصة</p>
    </div>
    @if ($tab === 'categories')
      <button type="button" class="btn btn--primary js-open-modal" data-modal="category-modal"
              data-action="{{ route('admin.categories.store') }}" data-method="POST" data-title="إضافة تصنيف جديد"
              data-field-category-name="" data-field-icon="📦">
        <x-icon name="plus" />إضافة تصنيف
      </button>
    @else
      <button type="button" class="btn btn--primary js-open-modal" data-modal="condition-modal"
              data-action="{{ route('admin.conditions.store') }}" data-method="POST" data-title="إضافة حالة جديدة"
              data-field-condition-name="" data-field-order="{{ $conditions->max('order') + 1 }}">
        <x-icon name="plus" />إضافة حالة
      </button>
    @endif
  </div>

  <nav class="segmented" aria-label="الأقسام">
    <a href="{{ route('admin.categories.index') }}" @class(['is-active' => $tab === 'categories'])>التصنيفات <span class="chip__count">{{ $categories->count() }}</span></a>
    <a href="{{ route('admin.categories.index', ['tab' => 'conditions']) }}" @class(['is-active' => $tab === 'conditions'])>حالات الغرض <span class="chip__count">{{ $conditions->count() }}</span></a>
  </nav>

  <div class="table-wrap admin-section">
    @if ($tab === 'categories')
      <table class="data-table">
        <thead>
          <tr><th>الأيقونة</th><th>اسم التصنيف</th><th>عدد الإعلانات</th><th>الحالة</th><th>إجراء</th></tr>
        </thead>
        <tbody>
          @foreach ($categories as $category)
            <tr>
              <td><span class="icon-preview">{{ $category->iconLabel() }}</span></td>
              <td><strong>{{ $category->category_name }}</strong></td>
              <td><span class="tag">{{ $category->items_count }} إعلانات</span></td>
              <td>
                <span @class(['status', 'status--success' => $category->state === 'active', 'status--danger' => $category->state === 'banned'])>
                  {{ $category->state === 'active' ? 'معتمد' : 'موقوف' }}
                </span>
              </td>
              <td>
                <div class="table-actions">
                  <button type="button" class="btn btn--light btn--xs js-open-modal" data-modal="category-modal"
                          data-action="{{ route('admin.categories.update', $category) }}" data-method="PUT" data-title="تعديل التصنيف"
                          data-field-category-name="{{ $category->category_name }}" data-field-icon="{{ $category->icon }}">
                    <x-icon name="pencil" />تعديل
                  </button>
                  <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn--soft btn--xs">{{ $category->state === 'active' ? 'إيقاف' : 'تفعيل' }}</button>
                  </form>
                  @if ($category->items_count === 0)
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="js-confirm" data-confirm="هل تريد حذف هذا التصنيف؟">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn--text-danger btn--xs">حذف</button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <table class="data-table">
        <thead>
          <tr><th>الترتيب</th><th>اسم الحالة</th><th>عدد الإعلانات</th><th>إجراء</th></tr>
        </thead>
        <tbody>
          @foreach ($conditions as $condition)
            <tr>
              <td><span class="icon-preview">{{ $condition->order }}</span></td>
              <td><strong>{{ $condition->condition_name }}</strong></td>
              <td><span class="tag">{{ $condition->items_count }} إعلانات</span></td>
              <td>
                <div class="table-actions">
                  <button type="button" class="btn btn--light btn--xs js-open-modal" data-modal="condition-modal"
                          data-action="{{ route('admin.conditions.update', $condition) }}" data-method="PUT" data-title="تعديل الحالة"
                          data-field-condition-name="{{ $condition->condition_name }}" data-field-order="{{ $condition->order }}">
                    <x-icon name="pencil" />تعديل
                  </button>
                  @if ($condition->items_count === 0)
                    <form method="POST" action="{{ route('admin.conditions.destroy', $condition) }}" class="js-confirm" data-confirm="هل تريد حذف هذه الحالة؟">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn--text-danger btn--xs">حذف</button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="alert admin-section">
    <x-icon name="info" />
    <div><strong>ترتيب حالات الغرض:</strong> تظهر الحالات للمستخدمين مرتبة من الأفضل (جديد) إلى الأسوأ (يحتاج صيانة) حسب رقم الترتيب. لا يمكن حذف تصنيف أو حالة مستخدمة في إعلانات.</div>
  </div>
@endsection

@push('modals')
  {{-- نافذة إضافة / تعديل تصنيف --}}
  <div class="modal" id="category-modal" role="dialog" aria-modal="true" aria-labelledby="category-modal-title"
       @if ($errors->hasAny(['category_name', 'icon'])) data-open="true" @endif>
    <div class="modal__dialog">
      <form method="POST" action="{{ $oldAction ?? route('admin.categories.store') }}">
        @csrf
        <input type="hidden" name="_method" value="{{ old('_method', 'POST') }}">
        <input type="hidden" name="_action" value="{{ $oldAction ?? route('admin.categories.store') }}">
        <div class="modal__header">
          <span class="modal__header-icon"><x-icon name="plus" /></span>
          <h2 class="js-modal-title" id="category-modal-title">إضافة تصنيف جديد</h2>
          <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
        </div>
        <div class="modal__body">
          <div class="form-group @error('category_name') has-error @enderror">
            <label class="form-label" for="category-name">اسم التصنيف <span class="required">*</span></label>
            <input class="input" type="text" id="category-name" name="category_name" maxlength="60" value="{{ old('category_name') }}" placeholder="مثال: مستلزمات مكتبية ودراسة">
            <p class="form-error">@error('category_name'){{ $message }}@enderror</p>
            <p class="form-note">يظهر اسم التصنيف في قوائم التصفية واستكشاف الأغراض للمستخدمين.</p>
          </div>

          <fieldset class="form-group @error('icon') has-error @enderror">
            <legend class="form-label">الأيقونة <span class="required">*</span></legend>
            <div class="icon-picker">
              @foreach ($icons as $icon)
                <label>
                  <input type="radio" name="icon" value="{{ $icon }}" @checked(old('icon') === $icon)>
                  <span>{{ $icon }}</span>
                </label>
              @endforeach
            </div>
            <p class="form-error">@error('icon'){{ $message }}@enderror</p>
          </fieldset>

          <div class="alert alert--warning">
            <x-icon name="lightbulb" />
            <div>سيتاح التصنيف فوراً للمستخدمين عند إضافة إعلان جديد أو تصفية نتائج البحث في المنصة.</div>
          </div>
        </div>
        <div class="modal__footer">
          <button type="submit" class="btn btn--primary">حفظ</button>
          <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>

  {{-- نافذة إضافة / تعديل حالة --}}
  <div class="modal" id="condition-modal" role="dialog" aria-modal="true" aria-labelledby="condition-modal-title"
       @if ($errors->hasAny(['condition_name', 'order'])) data-open="true" @endif>
    <div class="modal__dialog">
      <form method="POST" action="{{ $oldAction ?? route('admin.conditions.store') }}">
        @csrf
        <input type="hidden" name="_method" value="{{ old('_method', 'POST') }}">
        <input type="hidden" name="_action" value="{{ $oldAction ?? route('admin.conditions.store') }}">
        <div class="modal__header">
          <span class="modal__header-icon"><x-icon name="plus" /></span>
          <h2 class="js-modal-title" id="condition-modal-title">إضافة حالة جديدة</h2>
          <button type="button" class="modal__close js-close-modal" aria-label="إغلاق"><x-icon name="x" /></button>
        </div>
        <div class="modal__body">
          <div class="form-group @error('condition_name') has-error @enderror">
            <label class="form-label" for="condition-name">اسم الحالة <span class="required">*</span></label>
            <input class="input" type="text" id="condition-name" name="condition_name" maxlength="60" value="{{ old('condition_name') }}" placeholder="مثال: مستعمل - حالة جيدة">
            <p class="form-error">@error('condition_name'){{ $message }}@enderror</p>
          </div>
          <div class="form-group @error('order') has-error @enderror">
            <label class="form-label" for="condition-order">الترتيب <span class="required">*</span></label>
            <input class="input" type="number" id="condition-order" name="order" min="0" max="100" value="{{ old('order', 1) }}">
            <p class="form-error">@error('order'){{ $message }}@enderror</p>
            <p class="form-note">الرقم الأصغر يظهر أولاً.</p>
          </div>
        </div>
        <div class="modal__footer">
          <button type="submit" class="btn btn--primary">حفظ</button>
          <button type="button" class="btn btn--soft js-close-modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
@endpush
