<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials + ['status' => 'active'], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->user()->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials or inactive account.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register', [
            'departments' => Department::orderBy('name')->get(),
            'semesters' => Semester::orderByDesc('year')->get(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'roll_no' => ['required', 'string', 'max:50', 'unique:students,roll_no'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'semester_id' => ['nullable', 'exists:semesters,id'],
            'program' => ['required', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $role = Role::where('slug', 'student')->firstOrFail();
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user = User::create([
                'role_id' => $role->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'verification_otp' => Hash::make($otp),
                'verification_otp_expires_at' => now()->addMinutes(10),
                'otp_last_sent_at' => now(),
            ]);

            Student::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'] ?? Department::first()?->id,
                'semester_id' => $data['semester_id'] ?? Semester::first()?->id,
                'roll_no' => $data['roll_no'],
                'program' => $data['program'],
            ]);
            
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationOtpMail($user, $otp));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Registration OTP Email failed: ' . $e->getMessage());
                // We don't abort registration, but we should inform the user
                session()->flash('mail_error', 'Your account was created, but we could not send the verification email. Please check SMTP configuration or try resending.');
            }

            return $user;
        });

        Auth::login($user);

        return redirect()->route('verification.notice')->with('status', session('mail_error') ?? 'Registration successful. Please verify your email.');
    }

    public function showVerifyEmail()
    {
        if (Auth::user()->email_verified_at) {
            return redirect()->route('dashboard');
        }
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate(['otp' => ['required', 'string', 'size:6']]);
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        if (!$user->verification_otp || !$user->verification_otp_expires_at || now()->greaterThan($user->verification_otp_expires_at)) {
            return back()->with('error', 'The verification code has expired. Please request a new one.');
        }

        if (Hash::check($request->otp, $user->verification_otp)) {
            $user->update([
                'email_verified_at' => now(),
                'verification_otp' => null,
                'verification_otp_expires_at' => null,
            ]);
            return redirect()->route('dashboard')->with('status', 'Email verified successfully.');
        }

        return back()->withErrors(['otp' => 'The provided verification code is incorrect.']);
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return redirect()->route('dashboard');
        }

        if ($user->otp_last_sent_at && now()->diffInSeconds($user->otp_last_sent_at) < 60) {
            return back()->with('error', 'Please wait before requesting a new code.');
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'verification_otp' => Hash::make($otp),
            'verification_otp_expires_at' => now()->addMinutes(10),
            'otp_last_sent_at' => now(),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationOtpMail($user, $otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend OTP Email failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to send email. Please check SMTP configuration. Error: ' . $e->getMessage());
        }

        return back()->with('status', 'A new verification code has been sent to your email.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $request->email)->first();

        // Always return the same status to prevent email enumeration
        $status = 'If an account exists with that email, a password reset code has been sent.';

        if ($user) {
            if ($user->otp_last_sent_at && now()->diffInSeconds($user->otp_last_sent_at) < 60) {
                return redirect()->route('password.reset', ['email' => $request->email])->with('status', $status);
            }

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'verification_otp' => Hash::make($otp),
                'verification_otp_expires_at' => now()->addMinutes(10),
                'otp_last_sent_at' => now(),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerificationOtpMail($user, $otp));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Forgot Password OTP Email failed: ' . $e->getMessage());
                // Return generic status anyway to avoid enumeration, but log it
            }
        }

        return redirect()->route('password.reset', ['email' => $request->email])->with('status', $status);
    }

    public function showResetPassword(Request $request)
    {
        if (!$request->has('email')) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Invalid reset request.']);
        }

        if (!$user->verification_otp || !$user->verification_otp_expires_at || now()->greaterThan($user->verification_otp_expires_at)) {
            return back()->with('error', 'The reset code has expired.');
        }

        if (Hash::check($request->otp, $user->verification_otp)) {
            $user->update([
                'password' => Hash::make($request->password),
                'verification_otp' => null,
                'verification_otp_expires_at' => null,
                // Automatically verify email if they proved they own it
                'email_verified_at' => $user->email_verified_at ?? now(), 
            ]);
            return redirect()->route('login')->with('status', 'Your password has been reset successfully. Please login.');
        }

        return back()->withErrors(['otp' => 'The provided reset code is incorrect.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google authentication failed or was cancelled.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Update google_id and mark email as verified if they are missing
            $updates = [];
            if (!$user->google_id) {
                $updates['google_id'] = $googleUser->getId();
            }
            if (!$user->email_verified_at) {
                $updates['email_verified_at'] = now();
                $updates['verification_otp'] = null;
                $updates['verification_otp_expires_at'] = null;
            }
            
            if (!empty($updates)) {
                $user->update($updates);
            }
        } else {
            // Create a new user
            $user = DB::transaction(function () use ($googleUser) {
                $role = Role::where('slug', 'student')->firstOrFail();
                $newUser = User::create([
                    'role_id' => $role->id,
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)), // Random secure password
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(), // Mark as verified since Google verified it
                ]);

                Student::create([
                    'user_id' => $newUser->id,
                    'department_id' => Department::first()?->id,
                    'semester_id' => Semester::first()?->id,
                    'roll_no' => 'G-' . strtoupper(Str::random(8)),
                    'program' => 'BS Computer Science',
                ]);

                return $newUser;
            });
        }

        Auth::login($user, true); // Login and remember the user
        session()->regenerate();
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('dashboard'));
    }
}
