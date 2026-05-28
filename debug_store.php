<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Cek kolom tabel orders
echo "=== KOLOM TABEL ORDERS ===\n";
$columns = Illuminate\Support\Facades\Schema::getColumnListing('orders');
echo implode(', ', $columns) . "\n\n";

// 2. Cek apakah ada user
echo "=== USERS ===\n";
$users = App\Models\User::all(['id','name','email','role','owner_id']);
foreach ($users as $u) {
    echo "ID:{$u->id} | {$u->name} | {$u->email} | role:{$u->role} | owner_id:{$u->owner_id}\n";
}

// 3. Cek config baseUrl yang digunakan Flutter
echo "\n=== CEK KONEKSI ===\n";
echo "Server IP: " . request()->server('SERVER_ADDR', 'unknown') . "\n";

// 4. Simulasi store
echo "\n=== SIMULASI STORE ===\n";
$user = App\Models\User::first();
if (!$user) {
    echo "TIDAK ADA USER! Buat user dulu.\n";
    exit;
}

Illuminate\Support\Facades\Auth::login($user);

$request = new Illuminate\Http\Request();
$request->merge([
    'customer_name' => 'Test Simulasi',
    'phone' => '081234567890',
    'service_name' => 'Cuci Setrika',
    'total_price' => 25000,
    'weight' => 3,
    'payment_method' => 'CASH',
    'cash_received' => 50000,
    'delivery_option' => 'Ambil Sendiri',
]);
$request->headers->set('Accept', 'application/json');

$controller = new App\Http\Controllers\Api\TransactionController();

try {
    $response = $controller->store($request);
    $body = json_decode($response->getContent(), true);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Response: " . json_encode($body, JSON_PRETTY_PRINT) . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
