<?php

namespace App\Policies;

use App\Models\Petitions; // O App\Models\Petitions si usaste ese nombre
use App\Models\User;

class PeticionePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Petitions $peticione): bool
    {
        return true; // Todo el mundo puede ver [cite: 92]
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; 
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Petitions $peticione): bool
    {
        return $peticione->user_id == $user->id; 
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Petitions $peticione): bool
    {
        return $peticione->user_id == $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Petitions $peticione): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Petitions $peticione): bool
    {
        return false; 
    }

    public function firmar(User $user, Petitions $peticione)
    {
        return $user->id != $peticione->user_id;
    }
}