<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke order
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Relasi opsional ke services
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            
            // Snapshot data untuk histori jika suatu saat service diubah/dihapus
            $table->string('service_name');
            $table->unsignedInteger('price');
            $table->decimal('qty_or_weight', 8, 3);
            $table->string('unit'); // 'PCS' atau 'KG'
            $table->unsignedInteger('subtotal');
            $table->string('estimation_name')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
