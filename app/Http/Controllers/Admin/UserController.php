<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List all non-system users with their lock status.
     */
    public function index(): View
    {
        $users = User::role(['admin', 'assessor', 'client'])   // excludes 'system'
            ->orderBy('locked_at', 'desc')                     // locked users float to top
            ->orderBy('first_name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Unlock a locked account and reset the failure counter.
     */
    public function unlock(User $user): RedirectResponse
    {
        // Prevent unlocking system accounts via this route
        if ($user->isSystem()) {
            return back()->with('error', 'System accounts cannot be managed here.');
        }

        $user->unlock();

        return back()->with('success', "Account for {$user->name} has been unlocked successfully.");
    }
}