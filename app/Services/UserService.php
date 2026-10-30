<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function getUsers(): LengthAwarePaginator
    {
        return User::query()
            ->with('roles')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20);
    }
}
