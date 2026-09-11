<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Catalogue;

class CataloguePolicy
{
    // viewAny & create HANYA membutuhkan instance $user
    public function viewAny(User $user): bool
    {
        return $user->can('view_catalogues');
    }

    public function create(User $user): bool
    {
        return $user->can('create_catalogues');
    }

    // view, update, delete MEMBUTUHKAN instance $catalogue
    public function view(User $user, Catalogue $catalogue): bool
    {
        return $user->can('view_catalogues');
    }

    public function update(User $user, Catalogue $catalogue): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return $user->can('edit_catalogues') && $catalogue->user_id === $user->id;
    }

    public function delete(User $user, Catalogue $catalogue): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('delete_catalogues') && $catalogue->user_id === $user->id;
    }
}