<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\GenerateEmailTwoFactorCode;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailTwoFactorChallengeController extends Controller
{
    /**
     * Resend the email OTP code to the user currently mid-way through the
     * pending two-factor challenge (session('login.id')). Reuses the same
     * pending-login session state Fortify's own challenge flow relies on.
     */
    public function resend(Request $request, GenerateEmailTwoFactorCode $generate): RedirectResponse
    {
        $user = User::find($request->session()->get('login.id'));

        if (! $user || ! $user->usesEmailTwoFactor()) {
            return redirect()->route('login');
        }

        $generate($user);

        return back()->with('status', 'A new code has been sent to your email.');
    }
}
