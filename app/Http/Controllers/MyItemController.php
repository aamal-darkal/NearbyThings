<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemRequestForm;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Item;
use App\Models\Notification;
use App\Models\User;
use App\Support\Geo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * إدارة إعلانات المالك: إضافة، تعديل، حذف، إيقاف مؤقت، وصفحة "أغراضي"
 */
class MyItemController extends Controller
{
    // حالات الطلب اللي بتعتبر "صفقة جارية"
    private const OPEN_STATUSES = ['waiting', 'approved', 'received'];

    /**
     * صفحة أغراضي وإعلاناتي
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // كل أغراض المستخدم مع عدد الطلبات المفتوحة على كل غرض
        $query = $user->items()
            ->with(['images', 'category', 'owner'])
            ->addSelect(['open_requests_count' => DB::table('requests')
                ->selectRaw('count(*)')
                ->whereColumn('requests.item_id', 'items.id')
                ->whereIn('status', self::OPEN_STATUSES),
            ]);

        // البحث بالعنوان
        if ($request->filled('q')) {
            $query->where('title', 'like', '%'.$request->q.'%');
        }

        // الترتيب
        $request->input('sort') === 'oldest' ? $query->oldest() : $query->latest();

        $allItems = $query->get();

        // الأعداد لبطاقات الإحصائيات والتبويبات
        $counts = [
            'all' => $allItems->count(),
            'active' => $allItems->where('state', 'active')->where('open_requests_count', 0)->count(),
            'deal' => $allItems->where('open_requests_count', '>', 0)->count(),
            'inactive' => $allItems->whereIn('state', ['inactive', 'banned'])->count(),
            'completed' => $allItems->whereIn('state', ['sold', 'rented'])->where('open_requests_count', 0)->count(),
        ];

        // فلترة حسب التبويب
        $tab = $request->input('tab', 'all');
        $items = match ($tab) {
            'active' => $allItems->where('state', 'active')->where('open_requests_count', 0),
            'deal' => $allItems->where('open_requests_count', '>', 0),
            'inactive' => $allItems->whereIn('state', ['inactive', 'banned']),
            'completed' => $allItems->whereIn('state', ['sold', 'rented'])->where('open_requests_count', 0),
            default => $allItems,
        };

        return view('items.mine', [
            'items' => $items,
            'counts' => $counts,
            'tab' => $tab,
        ]);
    }

    /**
     * نموذج إضافة غرض جديد
     */
    public function create(Request $request): View
    {
        return view('items.form', $this->formData($request->user(), new Item([
            'transaction_type' => 'sale',
            'count' => 1,
            'rent_unit' => 'day',
        ])));
    }

    /**
     * حفظ الغرض الجديد
     */
    public function store(ItemRequestForm $request): RedirectResponse
    {
        $user = $request->user();

        $item = new Item(['owner_id' => $user->id, 'state' => 'active']);
        $this->fillItem($item, $request);
        $item->save();

        $this->saveImages($item, $request);

        ActivityLog::record('create', $item, null, ['title' => $item->title, 'state' => $item->state]);

        return redirect()->route('items.mine')->with('toast', 'تم نشر إعلانك بنجاح');
    }

    /**
     * نموذج تعديل غرض
     */
    public function edit(Request $request, Item $item): View|RedirectResponse
    {
        $this->authorizeOwner($request, $item);

        if ($reason = $this->cannotEditReason($item)) {
            return redirect()->route('items.mine')->with(['toast' => $reason, 'toast_type' => 'error']);
        }

        return view('items.form', $this->formData($request->user(), $item));
    }

    /**
     * حفظ التعديلات
     */
    public function update(ItemRequestForm $request, Item $item): RedirectResponse
    {
        $this->authorizeOwner($request, $item);

        if ($reason = $this->cannotEditReason($item)) {
            return redirect()->route('items.mine')->with(['toast' => $reason, 'toast_type' => 'error']);
        }

        $old = $item->only(['title', 'price', 'transaction_type', 'category_id']);

        $this->fillItem($item, $request);
        $item->save();

        // حذف الصور اللي اختار المستخدم يشيلها
        $removed = $item->images()->whereIn('id', $request->input('remove_images', []))->get();
        foreach ($removed as $image) {
            Storage::disk('uploads')->delete($image->image_path);
            $image->delete();
        }

        $this->saveImages($item, $request);

        ActivityLog::record('update', $item, $old, $item->only(['title', 'price', 'transaction_type', 'category_id']));

        return redirect()->route('items.mine')->with('toast', 'تم حفظ التعديلات');
    }

    /**
     * إيقاف مؤقت أو إعادة تفعيل الإعلان
     */
    public function toggle(Request $request, Item $item): RedirectResponse
    {
        $this->authorizeOwner($request, $item);

        if (! in_array($item->state, ['active', 'inactive'])) {
            return back()->with(['toast' => 'لا يمكن تغيير حالة هذا الإعلان', 'toast_type' => 'error']);
        }

        if ($item->state === 'active' && $item->hasOpenRequests()) {
            return back()->with(['toast' => 'لا يمكن إيقاف إعلان عليه طلبات جارية', 'toast_type' => 'error']);
        }

        $oldState = $item->state;
        $item->state = $item->state === 'active' ? 'inactive' : 'active';
        $item->save();

        ActivityLog::record('update', $item, ['state' => $oldState], ['state' => $item->state]);

        $message = $item->state === 'active' ? 'تم تفعيل الإعلان' : 'تم إيقاف الإعلان مؤقتاً، ولن يظهر في البحث';

        return back()->with('toast', $message);
    }

    /**
     * حذف الإعلان (فقط اذا ما عليه أي طلبات)
     */
    public function destroy(Request $request, Item $item): RedirectResponse
    {
        $this->authorizeOwner($request, $item);

        if ($item->hasAnyRequests()) {
            return back()->with([
                'toast' => 'لا يمكن حذف إعلان مرتبط بطلبات، يمكنك إيقافه مؤقتاً بدلاً من ذلك',
                'toast_type' => 'error',
            ]);
        }

        ActivityLog::record('delete', $item, ['title' => $item->title, 'state' => $item->state]);

        foreach ($item->images as $image) {
            Storage::disk('uploads')->delete($image->image_path);
        }

        $item->delete();

        return redirect()->route('items.mine')->with('toast', 'تم حذف الإعلان');
    }

    // ===== دوال مساعدة =====

    // بس صاحب الغرض بيقدر يعدل عليه
    private function authorizeOwner(Request $request, Item $item): void
    {
        abort_unless($item->owner_id === $request->user()->id, 403);
    }

    // سبب منع التعديل (أو null اذا مسموح)
    private function cannotEditReason(Item $item): ?string
    {
        if ($item->state === 'banned') {
            return 'هذا الإعلان محجوب من قبل الإدارة ولا يمكن تعديله';
        }

        if (in_array($item->state, ['sold', 'rented'])) {
            return 'لا يمكن تعديل إعلان صفقته مكتملة';
        }

        if ($item->hasOpenRequests()) {
            return 'لا يمكن تعديل إعلان عليه طلبات جارية حفاظاً على حقوق الطرفين';
        }

        return null;
    }

    // البيانات اللي بيحتاجها نموذج الإضافة والتعديل
    private function formData(User $user, Item $item): array
    {
        return [
            'item' => $item,
            'user' => $user,
            'categories' => Category::active()->orderBy('category_name')->get(),
            'conditions' => Condition::orderBy('order')->get(),
        ];
    }

    // تعبئة حقول الغرض من النموذج
    private function fillItem(Item $item, ItemRequestForm $request): void
    {
        $type = $request->transaction_type;

        $item->fill([
            'title' => $request->title,
            'description' => $request->description,
            'condition_id' => $request->condition_id,
            'count' => $request->input('count'),
            'transaction_type' => $type,
            'price' => $type === 'exchange' ? 0 : $request->price,
            'max_rent_duration' => $type === 'rent' ? $request->max_rent_duration : null,
            'rent_unit' => $type === 'rent' ? $request->rent_unit : null,
            'location_hint' => $request->location_hint,
        ]);

        // المقابل المطلوب بالتبادل منخزنه بعمود variants
        $variants = $item->variants ?? [];
        $variants['exchange_for'] = $type === 'exchange' ? $request->exchange_for : null;
        $item->variants = array_filter($variants);

        // التصنيف: موجود، أو مقترح جديد بينتظر موافقة المشرف
        if ($request->filled('suggested_category')) {
            $item->category_id = $this->suggestCategory($request->suggested_category, $request->user());
        } else {
            $item->category_id = $request->category_id;
        }

        // الموقع: عنوان الحساب أو موقع آخر من الخريطة (مقرّب للخصوصية)
        $user = $request->user();
        if ($request->location_mode === 'custom') {
            $item->latitude = Geo::approximate((float) $request->latitude);
            $item->longitude = Geo::approximate((float) $request->longitude);
        } else {
            $item->latitude = $user->latitude;
            $item->longitude = $user->longitude;
        }
    }

    // تصنيف مقترح: بينضاف بحالة "new" وبيوصل إشعار للمشرفين
    private function suggestCategory(string $name, User $user): int
    {
        $name = trim($name);
        $category = Category::where('category_name', $name)->first();

        // اذا التصنيف موجود ومعتمد منستخدمه مباشرة
        if ($category && $category->state !== 'banned') {
            return $category->id;
        }

        if (! $category) {
            $category = Category::create(['category_name' => $name, 'state' => 'new']);

            $admins = User::where('is_admin', true)->pluck('id');
            foreach ($admins as $adminId) {
                Notification::send($adminId, 'اقتراح تصنيف جديد', 'اقترح '.$user->full_name.' تصنيفاً جديداً: "'.$name.'".');
            }

            return $category->id;
        }

        // التصنيف موقوف: منحطه بـ "أخرى"
        return Category::where('category_name', 'أخرى')->value('id') ?? Category::active()->value('id');
    }

    // حفظ الصور المرفوعة بمجلد public/uploads/items
    private function saveImages(Item $item, ItemRequestForm $request): void
    {
        foreach ($request->file('images', []) as $file) {
            $path = 'items/'.Str::uuid().'.'.$file->extension();
            Storage::disk('uploads')->put($path, file_get_contents($file->getRealPath()));

            $item->images()->create(['image_path' => $path]);
        }
    }
}
