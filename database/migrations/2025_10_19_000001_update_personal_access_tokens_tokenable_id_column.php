<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure Sanctum tokenable_id supports string-based primary keys.
     */
    public function up(): void
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('tokenable_id')->change();
        });
    }

    /**
     * Revert the tokenable_id column to the original type.
     */
    public function down(): void
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('tokenable_id')->change();
        });
    }
};
