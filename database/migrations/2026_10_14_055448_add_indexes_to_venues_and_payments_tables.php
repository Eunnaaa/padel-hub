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
        Schema::table('venues', function (Blueprint $table) {
            $table->index(['status', 'city']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('transaction_reference');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropIndex(['status', 'city']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['transaction_reference']);
        });
    }
};
