<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->boolean('requires_approval')
                ->default(false)
                ->after('is_adjustment');

            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->after('requires_approval');

            $table->foreignId('approved_by')
                ->nullable()
                ->after('approval_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            $table->text('approval_note')
                ->nullable()
                ->after('approved_at');

            $table->index(['requires_approval', 'approval_status']);
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex(['requires_approval', 'approval_status']);
            $table->dropIndex(['approved_by']);
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approval_note', 'approved_at', 'approval_status', 'requires_approval']);
        });
    }
};
