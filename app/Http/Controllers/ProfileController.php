<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ItemRequest;
use App\Models\Review;
use App\Support\Geo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * صفحة الملف الشخصي: بيانات المستخدم وإعلاناته النشطة
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        $items = $user->items()
            ->where('state', 'active')
            ->with(['images', 'owner'])
            ->latest()
            ->get();

        // التقييمات اللي وصلت للمستخدم مع العملية والمقيّم
        $reviews = Review::where('reviewed_user_id', $user->id)
            ->with(['reviewer', 'request.item'])
            ->latest()
            ->get();

        // عدد العمليات المكتملة (كمالك أو كباحث)
        $completedDeals = ItemRequest::involving($user)->where('status', 'finished')->count();

        return view('profile.show', [
            'user' => $user,
            'items' => $items,
            'reviews' => $reviews,
            'completedDeals' => $completedDeals,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // البيانات الشخصية
        $user->fill($request->safe()->only(['full_name', 'email', 'phone', 'address', 'city']));

        // الموقع منخزنه مقرّب (حوالي 100 متر) حفاظاً على الخصوصية
        if ($request->filled('latitude')) {
            $user->latitude = Geo::approximate((float) $request->latitude);
            $user->longitude = Geo::approximate((float) $request->longitude);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // كلمة المرور بتتغير بس اذا انكتبت كلمة جديدة (بتتشفر تلقائياً من الموديل)
        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
