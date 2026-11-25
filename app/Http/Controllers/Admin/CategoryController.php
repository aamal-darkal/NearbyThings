<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * إدارة التصنيفات وحالات الغرض
 */
class CategoryController extends Controller
{
    // الأيقونات المتاحة للاختيار
    public const ICONS = ['🛋️', '🔌', '🚲', '🔧', '📚', '🧸', '⛺', '🎒', '💼', '🎸', '📷', '🪴', '📱', '👕', '🧺', '📦'];

    public function index(Request $request): View
    {
        return view('admin.categories', [
            'tab' => $request->input('tab') === 'conditions' ? 'conditions' : 'categories',
            'categories' => Category::whereIn('state', ['active', 'banned'])->withCount('items')->orderBy('category_name')->get(),
            'conditions' => Condition::withCount('items')->orderBy('order')->get(),
            'icons' => self::ICONS,
        ]);
    }

    // ===== التصنيفات =====

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);

        $category = Category::create($data + ['state' => 'active']);
        ActivityLog::record('create', $category, null, $data);

        return back()->with('toast', 'تمت إضافة التصنيف');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateCategory($request, $category);

        $old = $category->only(['category_name', 'icon']);
        $category->update($data);
        ActivityLog::record('update', $category, $old, $data);

        return back()->with('toast', 'تم حفظ التصنيف');
    }

    // إيقاف التصنيف (ما بيظهر بالفلاتر ولا بالإضافة) أو إعادة تفعيله
    public function toggle(Category $category): RedirectResponse
    {
        $old = $category->state;
        $category->update(['state' => $category->state === 'active' ? 'banned' : 'active']);
        ActivityLog::record('update', $category, ['state' => $old], ['state' => $category->state]);

        return back()->with('toast', $category->state === 'active' ? 'تم تفعيل التصنيف' : 'تم إيقاف التصنيف');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->items()->exists()) {
            return back()->with(['toast' => 'لا يمكن حذف تصنيف مرتبط بإعلانات، يمكنك إيقافه بدلاً من ذلك', 'toast_type' => 'error']);
        }

        ActivityLog::record('delete', $category, $category->only(['category_name']));
        $category->delete();

        return back()->with('toast', 'تم حذف التصنيف');
    }

    // ===== حالات الغرض =====

    public function storeCondition(Request $request): RedirectResponse
    {
        $data = $this->validateCondition($request);

        $condition = Condition::create($data);
        ActivityLog::record('create', $condition, null, $data);

        return redirect()->route('admin.categories.index', ['tab' => 'conditions'])->with('toast', 'تمت إضافة الحالة');
    }

    public function updateCondition(Request $request, Condition $condition): RedirectResponse
    {
        $data = $this->validateCondition($request, $condition);

        $old = $condition->only(['condition_name', 'order']);
        $condition->update($data);
        ActivityLog::record('update', $condition, $old, $data);

        return redirect()->route('admin.categories.index', ['tab' => 'conditions'])->with('toast', 'تم حفظ الحالة');
    }

    public function destroyCondition(Condition $condition): RedirectResponse
    {
        if ($condition->items()->exists()) {
            return back()->with(['toast' => 'لا يمكن حذف حالة مستخدمة في إعلانات', 'toast_type' => 'error']);
        }

        ActivityLog::record('delete', $condition, $condition->only(['condition_name']));
        $condition->delete();

        return back()->with('toast', 'تم حذف الحالة');
    }

    // ===== التحقق =====

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'category_name' => ['required', 'string', 'min:2', 'max:60', Rule::unique('categories')->ignore($category)],
            'icon' => ['required', Rule::in(self::ICONS)],
        ], [
            'category_name.unique' => 'هذا التصنيف موجود مسبقاً',
            'category_name.*' => 'اسم التصنيف بين 2 و60 حرفاً',
            'icon.*' => 'اختر أيقونة للتصنيف',
        ]);
    }

    private function validateCondition(Request $request, ?Condition $condition = null): array
    {
        return $request->validate([
            'condition_name' => ['required', 'string', 'min:2', 'max:60', Rule::unique('conditions')->ignore($condition)],
            'order' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'condition_name.unique' => 'هذه الحالة موجودة مسبقاً',
            'condition_name.*' => 'اسم الحالة بين 2 و60 حرفاً',
            'order.*' => 'الترتيب رقم من 0 إلى 100',
        ]);
    }
}
