<?php

namespace App\Policies;

use App\Models\BoardingHouse;
use App\Models\User;

class BoardingHousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isMitra();
    }

    public function view(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->isAdmin() || $boardingHouse->mitra_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isMitra() && ($user->mitraProfile?->isApproved() ?? false);
    }

    public function update(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->isAdmin() || $boardingHouse->mitra_id === $user->id;
    }

    public function delete(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->isAdmin() || $boardingHouse->mitra_id === $user->id;
    }
}
