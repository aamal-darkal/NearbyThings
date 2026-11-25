<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * اقتراحات التصنيفات من المستخدمين (تصنيفات بحالة new)
 * الإعلان بينشر فوراً، وبيظهر بفلاتر التصنيفات بعد الموافقة
 */
class SuggestionController extends Controller
{
    public function index(): View
    {
        return view('admin.suggestions', [
            'suggestions' => Category::where('state', 'new')
                ->with(['items' => fn ($q) => $q->with('owner')->latest()])
                ->latest()
                ->get(),
            'categories' => Category::active()->orderBy('category_name')->get(),
            'approvedCount' => Category::where('state', 'active')->where('updated_at', '>=', now()->subMonth())->count(),
        ]);
    }

    /**
     * الموافقة: التصنيف بيصير معتمد وبيظهر بالفلاتر
     */
    public function approve(Category $category): RedirectResponse
    {
        abort_unless($category->state === 'new', 404);

        $category->update(['state' => 'active', 'icon' => $category->icon ?: '📦']);
        ActivityLog::record('update', $category, ['state' => 'new'], ['state' => 'active']);

        $this->notifyOwners($category, 'تمت الموافقة على التصنيف', 'تمت الموافقة على التصنيف المقترح "'.$category->category_name.'"، وأصبح إعلانك يظهر في فلاتر التصنيفات.');

        return back()->with('toast', 'تم اعتماد التصنيف');
    }

    /**
     * النقل لتصنيف موجود: الإعلانات بتنتقل، والاقتراح بينحذف
     */
    public function move(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->state === 'new', 404);

        $data = $request->validate([
            'target_id' => ['required', Rule::exists('categories', 'id')->where('state', 'active')],
        ], [
            'target_id.*' => 'اختر التصنيف',
        ]);

        $target = Category::find($data['target_id']);

        $this->notifyOwners($category, 'تم نقل إعلانك لتصنيف آخر', 'تم نقل إعلانك من التصنيف المقترح "'.$category->category_name.'" إلى "'.$target->category_name.'".');

        $category->items()->update(['category_id' => $target->id]);
        ActivityLog::record('delete', $category, ['category_name' => $category->category_name, 'moved_to' => $target->category_name]);
        $category->delete();

        return back()->with('toast', 'تم نقل الإعلانات إلى "'.$target->category_name.'"');
    }

    // إشعار لأصحاب الإعلانات اللي بهالتصنيف
    private function notifyOwners(Category $category, string $title, string $message): void
    {
        $ownerIds = $category->items()->distinct()->pluck('owner_id');

        foreach ($ownerIds as $ownerId) {
            Notification::send($ownerId, $title, $message);
        }
    }
}
