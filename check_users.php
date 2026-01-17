<?php

require 'bootstrap/app.php';

$app = new Illuminate\Foundation\Application(
    dirname(__DIR__)
);

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::all(['id', 'name', 'email', 'roles']);

echo "=== DATA PENGGUNA SETELAH RESTRUKTURISASI ===\n\n";
echo json_encode($users->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n=== RINGKASAN ===\n";
echo "Total pengguna: " . $users->count() . "\n";
echo "Roles yang ada: " . implode(", ", $users->pluck('roles')->unique()->toArray()) . "\n";
