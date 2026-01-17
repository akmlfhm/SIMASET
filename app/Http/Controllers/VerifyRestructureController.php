<?php

namespace App\Http\Controllers;

use App\Models\User;

class VerifyRestructureController extends Controller
{
    public function checkUsers()
    {
        $users = User::all(['id', 'name', 'email', 'roles']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data pengguna setelah restrukturisasi role',
            'total_users' => $users->count(),
            'roles_exist' => $users->pluck('roles')->unique()->values()->toArray(),
            'users' => $users->toArray()
        ]);
    }
}
