<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        if(Auth::user()->can('manage-profile')){
            return Inertia::render('profile/edit', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => session('status'),
                'twoFactorEnabled' => (bool) $request->user()->two_factor_confirmed_at,
                'twoFactorSetup' => session('two_factor_setup'),
                'recoveryCodes' => session('two_factor_recovery_codes', []),
            ]);
        }else{
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function twoFactor(Request $request)
    {
        $data = $request->validate(['action' => 'required|in:setup,confirm,disable', 'password' => 'required|current_password', 'code' => 'nullable|string|max:32']);
        $user = $request->user(); $result = [];
        if ($data['action'] === 'setup') {
            abort_if($user->two_factor_confirmed_at, 409, 'Disable the existing authenticator before replacing it.');
            $secret = \App\Models\User::newTwoFactorSecret();
            $user->forceFill(['two_factor_secret' => $secret, 'two_factor_last_used_at' => null])->save();
            $result = ['secret' => $secret, 'uri' => 'otpauth://totp/'.rawurlencode('Mutamark ERP:'.$user->email).'?secret='.$secret.'&issuer=Mutamark%20ERP&algorithm=SHA1&digits=6&period=30'];
        } elseif ($data['action'] === 'confirm') {
            abort_if($user->two_factor_confirmed_at, 409, 'Authenticator is already enabled.');
            if (!$user->verifySecondFactor($data['code'] ?? '', false)) throw \Illuminate\Validation\ValidationException::withMessages(['code' => 'Enter a valid authenticator code.']);
            $codes = array_map(fn () => bin2hex(random_bytes(8)), range(1, 8));
            $user->forceFill(['two_factor_confirmed_at' => now(), 'two_factor_recovery_codes' => array_map(fn ($code) => \Illuminate\Support\Facades\Hash::make($code), $codes)])->save();
            $result = ['recovery_codes' => $codes];
        } else {
            if (!$user->verifySecondFactor($data['code'] ?? '')) throw \Illuminate\Validation\ValidationException::withMessages(['code' => 'Enter an authenticator or recovery code.']);
            $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null, 'two_factor_recovery_codes' => null, 'two_factor_last_used_at' => null])->save();
        }
        if ($request->expectsJson()) return response()->json($result);
        return back()->with('success', __('Authenticator settings updated.'))->with('two_factor_setup', $result['secret'] ?? null)->with('two_factor_recovery_codes', $result['recovery_codes'] ?? []);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);
        // Financial ownership and audit attribution must survive account closure.
        $request->user()->forceFill(['is_enable_login' => false])->save();
        $request->user()->tokens()->delete();
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect('/login')->with('status', __('Account deactivated; business history retained.'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        if(Auth::user()->can('edit-profile') && Auth::user()->id === $request->user()->id){
            $user = $request->user();
            $validated = $request->validated();

            if (isset($validated['avatar']) && $validated['avatar']) {
                $validated['avatar'] = basename($validated['avatar']);
            }

            $user->fill($validated);
            if ($user->isDirty('email') && admin_setting('enableEmailVerification') == 'on') {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route('profile.edit')->with('success', __('The profile details are updated successfully.'));
        }
        else{
            return Redirect::route('profile.edit')->with('error', __('Permission denied'));
        }
    }
}
