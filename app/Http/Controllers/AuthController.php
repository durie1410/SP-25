<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\AuditService;
use App\Notifications\WelcomeNotification;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-register');
    }

    public function login(Request $request)
    {
        // Validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // DEBUG: Log login attempt
        Log::info('=== LOGIN ATTEMPT DEBUG ===');
        Log::info('Email input: ' . $credentials['email']);
        Log::info('Password input length: ' . strlen($credentials['password']));
        
        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();
        if ($user) {
            Log::info('User found: ID=' . $user->id . ', Name=' . $user->name . ', Role=' . $user->role);
            Log::info('Password hash in DB: ' . substr($user->password, 0, 30) . '...');
            Log::info('Password hash algo: ' . (password_get_info($user->password)['algoName'] ?? 'unknown'));
            Log::info('Manual Hash::check result: ' . (Hash::check($credentials['password'], $user->password) ? 'TRUE' : 'FALSE'));
        } else {
            Log::info('User NOT found with email: ' . $credentials['email']);
        }
        
        // Log guard being used
        Log::info('Guard being used: ' . Auth::getDefaultDriver());

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            Log::info('Auth::attempt SUCCESS');
            
            // Log successful login
            AuditService::logLogin('User logged in successfully');
            
            // Get the authenticated user
            $user = Auth::user();
            
            // Redirect based on user role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'staff' || $user->isStaff()) {
                // Staff cũng dùng admin dashboard
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('home');
            }
        }
        
        Log::info('Auth::attempt FAILED');
        Log::info('=== END LOGIN DEBUG ===');

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        return view('auth.login-register', ['showRegister' => true]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user', // SECURITY FIX: Always 'user'
            ]);

            // Assign role using Spatie Permission
            $user->assignRole('user');

            // Log user registration AFTER user is created and committed
            AuditService::logCreated($user, 'New user registered', $user->id);

            DB::commit();

            // Send welcome email notification
            try {
                $user->notify(new WelcomeNotification($user));
            } catch (\Exception $e) {
                // Log email error but don't fail registration
                Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

            Auth::login($user);
            
            // Regenerate session sau khi login
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Đăng ký thành công! Email chào mừng đã được gửi đến hộp thư của bạn.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.',
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        // Log logout before destroying session
        AuditService::logLogout('User logged out');
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }

    /**
     * Display the password reset link request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            AuditService::log('password_reset', User::class, null, null, 'User reset password');
            return redirect()->route('login')->with('status', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Show the email verification notice.
     */
    public function showVerificationNotice()
    {
        return auth()->user()->hasVerifiedEmail()
            ? redirect()->route('home')
            : view('auth.verify-email');
    }

    /**
     * Mark the user's email address as verified.
     */
    public function verifyEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        return redirect()->route('home')->with('verified', true);
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
