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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('kg', 8, 2)->default(0);
            $table->string('type')->nullable(); // Plastic type
            $table->string('source')->nullable();
            $table->decimal('sold_kg', 8, 2)->default(0);
            $table->decimal('revenue', 10, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
