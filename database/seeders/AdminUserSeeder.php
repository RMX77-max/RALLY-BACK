<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name  = env('ADMIN_NAME', 'Administrador');
        $email = env('ADMIN_EMAIL', 'admin@rally.com');
        $pass  = env('ADMIN_PASSWORD', 'Stewart77#');

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make($pass), 'role' => 'admin']
        );

        if (!$user->wasRecentlyCreated) {
            $updates = [];
            if ($user->role !== 'admin') $updates['role'] = 'admin';
            if ($pass && $pass !== 'Stewart77#') $updates['password'] = Hash::make($pass);
            if ($updates) $user->update($updates);
        }
    }
}
