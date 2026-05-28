<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel shops menyimpan pengaturan toko milik Owner.
     * Setiap Owner punya 1 record shop (one-to-one).
     * Data ini sebelumnya ada di tabel users, dipindahkan agar lebih rapi.
     */
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Owner ID
            $table->string('shop_name')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->unsignedInteger('delivery_fee_per_km')->default(0);
            $table->integer('free_delivery_km')->default(0);
            $table->boolean('is_delivery_active')->default(false);
            $table->boolean('is_estimation_active')->default(false);
            $table->integer('express_extra_price')->default(0);
            $table->integer('kilat_extra_price')->default(0);
            $table->string('device_id')->nullable();
            $table->timestamps();

            // Satu owner hanya punya satu toko
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
