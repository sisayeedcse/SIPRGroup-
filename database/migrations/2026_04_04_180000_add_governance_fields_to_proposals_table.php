<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table): void {
            $table->unsignedInteger('quorum_required')->default(3)->after('status');
            $table->date('closes_at')->nullable()->after('quorum_required');
            $table->timestamp('finalized_at')->nullable()->after('closes_at');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table): void {
            $table->dropColumn(['quorum_required', 'closes_at', 'finalized_at']);
        });
    }
};
