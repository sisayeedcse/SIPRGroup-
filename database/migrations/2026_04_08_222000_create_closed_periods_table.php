<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closed_periods', function (Blueprint $table): void {
            $table->id();
            $table->date('month');
            $table->foreignId('closed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('closed_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('month');
            $table->index('closed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closed_periods');
    }
};
