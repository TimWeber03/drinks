<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcodes', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('type');
            $table->unsignedBigInteger('linked');
            $table->timestamps();

            $table->index(['type', 'linked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcodes');
    }
};
