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
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    private function isMobileRequest(Request $request): bool
    {
        $ua = (string) $request->header('User-Agent', '');

        return (bool) preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
    }

    /**
     * Display the registration view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->isMobileRequest($request)) {
            return view('auth.register');
        }

        return redirect()->route('home')->with('showRegisterModal', true);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ], [
                'name.required' => 'Vui lòng nhập họ và tên.',
                'name.max' => 'Họ và tên không được vượt quá :max ký tự.',
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Email không đúng định dạng.',
                'email.unique' => 'Email này đã được đăng ký. Vui lòng dùng email khác.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.confirmed' => 'Vui lòng nhập lại mật khẩu đúng với mật khẩu đã nhập.',
                'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
                'password.letters' => 'Mật khẩu phải chứa ít nhất một chữ cái.',
                'password.mixed' => 'Mật khẩu phải chứa cả chữ hoa và chữ thường.',
                'password.numbers' => 'Mật khẩu phải chứa ít nhất một chữ số.',
                'password.symbols' => 'Mật khẩu phải chứa ít nhất một ký tự đặc biệt.',
                'password.uncompromised' => 'Mật khẩu này không an toàn. Vui lòng chọn mật khẩu khác.',
            ], [
                'name' => 'Họ và tên',
                'email' => 'Email',
                'password' => 'Mật khẩu',
                'password_confirmation' => 'Nhập lại mật khẩu',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($this->isMobileRequest($request)) {
                return redirect()->route('register')
                    ->withErrors($e->validator)
                    ->withInput();
            }

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('showRegisterModal', true);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        // Không tự động đăng nhập
        // Auth::login($user);
        if ($this->isMobileRequest($request)) {
            return redirect()->route('login')
                ->with('status', 'Đăng ký thành công! Vui lòng đăng nhập.');
        }

        return redirect()->back()
            ->with('status', 'Đăng ký thành công! Vui lòng đăng nhập.')
            ->with('showLoginModal', true);
    }
}
