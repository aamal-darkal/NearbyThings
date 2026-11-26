<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * تسجيل الدخول عبر الـ API باستخدام Laravel Sanctum (توكن)
 * مفيد للاختبار من Postman أو لتطبيق موبايل لاحقاً
 */
class AuthController extends Controller
{
    // تسجيل الدخول: بيرجع توكن
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ], [
            'email.*' => 'أدخل بريداً إلكترونياً صحيحاً',
            'password.*' => 'أدخل كلمة المرور',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            ]);
        }

        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => 'تم حظر حسابك من قبل إدارة الموقع',
            ]);
        }

        $token = $user->createToken($request->input('device_name', 'api'))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userData($user),
        ]);
    }

    // بيانات المستخدم الحالي
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->userData($request->user()));
    }

    // تسجيل الخروج: حذف التوكن الحالي
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج']);
    }

    // البيانات اللي منرجعها عن المستخدم (بدون كلمة المرور)
    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'city' => $user->city,
            'rate' => $user->rate,
            'is_admin' => $user->is_admin,
        ];
    }
}
