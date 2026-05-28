<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // FK ke tabel users (Owner pemilik order ini)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('phone')->nullable();

            // Layanan sekarang dipindah ke tabel order_items (1-to-many)
            // Hanya menyimpan total harga dan total berat
            $table->timestamp('estimation_time')->nullable();
            $table->decimal('total_weight', 8, 3)->default(0);

            // Harga & Pembayaran (unsigned karena tidak pernah negatif)
            $table->unsignedInteger('delivery_fee')->default(0);
            $table->unsignedInteger('total_price')->default(0);
            $table->string('status')->default('ANTRIAN')->index();
            $table->string('payment_method')->default('CASH');
            $table->unsignedInteger('cash_received')->default(0);
            $table->unsignedInteger('cash_change')->default(0);
            
            // Tipe Pengantaran (satu kolom saja, label Indonesia ditampilkan di UI)
            // Values: none, pickup, delivery, both
            $table->string('delivery_type')->default('none')->index();

            // Kolom Lokasi untuk Fitur Map & Tracking Driver
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('delivery_distance', 8, 2)->nullable(); // Jarak toko-pelanggan (km)
            
            $table->timestamps();

            // Index untuk performa query laporan
            $table->index('payment_method');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};