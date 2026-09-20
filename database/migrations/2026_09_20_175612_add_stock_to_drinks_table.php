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
        Schema::table('drinks', function (Blueprint $table) {
            $table->boolean('stock_tracked')->default(false)->after('active');
            $table->integer('stock')->default(0)->after('stock_tracked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->dropColumn(['stock_tracked', 'stock']);
        });
    }
};
