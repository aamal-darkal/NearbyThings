<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'regex:/^09[0-9]{8}$/'],
            'address' => ['required', 'string', 'max:255'],
        ], [
            'full_name.*' => 'أدخل اسم المستخدم',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم مسبقاً',
            'email.lowercase' => 'اكتب البريد الإلكتروني بأحرف صغيرة',
            'email.*' => 'أدخل بريداً إلكترونياً صحيحاً',
            'phone.*' => 'رقم الهاتف يجب أن يبدأ بـ 09 ويتكون من 10 أرقام',
            'password.confirmed' => 'كلمتا المرور غير متطابقتين',
            'password.*' => 'كلمة المرور 8 أحرف على الأقل',
            'address.*' => 'أدخل منطقتك',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('profile.show', absolute: false));
    }
}
