<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any applications.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        // Admin and assessors can view all applications
        if ($user->hasRole(['admin', 'assessor'])) {
            return true;
        }

        // Clients can only view their own applications
        return $user->id === $application->user_id;
    }

    /**
     * Determine whether the user can create applications.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('client');
    }

    /**
     * Determine whether the user can update the application.
     */
    public function update(User $user, Application $application): bool
    {
        // Admin can update any application
        if ($user->hasRole('admin')) {
            return true;
        }

        // Clients can only update their own applications if editable
        if ($user->id === $application->user_id) {
            return $application->isEditable();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the application.
     */
    public function delete(User $user, Application $application): bool
    {
        // Only admin or application owner can delete draft applications
        if ($application->status !== 'draft') {
            return false;
        }

        return $user->hasRole('admin') || $user->id === $application->user_id;
    }

    /**
     * Determine whether the user can review the application.
     */
    public function review(User $user, Application $application): bool
    {
        return $user->hasRole(['admin', 'assessor']);
    }

    /**
     * Determine whether the user can assign the application.
     */
    public function assign(User $user, Application $application): bool
    {
        // Only admins can assign
        if (!$user->hasRole('admin')) {
            return false;
        }

        // Cannot reassign approved or declined applications
        if (in_array($application->status, ['approved', 'declined'])) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can verify living expenses.
     */
    public function verifyExpenses(User $user, Application $application): bool
    {
        return $user->hasRole(['admin', 'assessor']);
    }
}
