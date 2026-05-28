<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::updateOrCreate(
    ['email' => 'test@owner.com'],
    [
        'name' => 'Test Owner',
        'password' => Hash::make('password'),
        'role' => 'owner'
    ]
);

$token = $user->createToken('test')->plainTextToken;
echo "TOKEN_START:" . $token . ":TOKEN_END";
