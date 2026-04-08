<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreignId('adjustment_for_id')
                ->nullable()
                ->after('id')
                ->constrained('transactions')
                ->nullOnDelete();

            $table->boolean('is_adjustment')
                ->default(false)
                ->after('note');

            $table->index('adjustment_for_id');
            $table->index('is_adjustment');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex(['adjustment_for_id']);
            $table->dropIndex(['is_adjustment']);
            $table->dropConstrainedForeignId('adjustment_for_id');
            $table->dropColumn('is_adjustment');
        });
    }
};
