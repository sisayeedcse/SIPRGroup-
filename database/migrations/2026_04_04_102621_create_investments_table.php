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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sector')->nullable();
            $table->string('partner')->nullable();
            $table->date('date');
            $table->decimal('capital_deployed', 10, 2);
            $table->decimal('expected_return', 10, 2)->nullable();
            $table->decimal('actual_return', 10, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'paused'])->default('active');
            $table->json('team_members')->nullable(); // Array of user IDs
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
