<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_option_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('role', 32);
            $table->string('option_key', 64);
            $table->timestamps();

            $table->unique(['role', 'option_key']);
            $table->index('role');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','finance','secretary','advisor','member') NOT NULL DEFAULT 'member'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role='member' WHERE role='advisor'");
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','finance','secretary','member') NOT NULL DEFAULT 'member'");
        }

        Schema::dropIfExists('role_option_permissions');
    }
};
