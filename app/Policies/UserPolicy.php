<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    // Hanya admin yang bisa melihat daftar semua user
    public function viewAny(User $user): bool
    {
        return $user->can('manage_users');
    }

    // Semua user terotentikasi bisa memperbarui datanya sendiri
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can('manage_users');
    }
}