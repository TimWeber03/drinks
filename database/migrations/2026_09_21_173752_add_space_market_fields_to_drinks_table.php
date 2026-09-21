<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->unsignedInteger('caffeine')->nullable()->after('bottle_size');
            $table->unsignedInteger('alcohol')->nullable()->after('caffeine');
            $table->unsignedInteger('energy')->nullable()->after('alcohol');
            $table->unsignedInteger('sugar')->nullable()->after('energy');
            $table->foreignId('image_id')->nullable()->after('sugar')->constrained()->nullOnDelete();
        });

        $this->migrateImagePathsToImages();

        Schema::table('drinks', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('drinks', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('bottle_size');
        });

        DB::table('drinks')
            ->join('images', 'images.id', '=', 'drinks.image_id')
            ->update(['drinks.image_path' => DB::raw('images.path')]);

        Schema::table('drinks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_id');
            $table->dropColumn(['caffeine', 'alcohol', 'energy', 'sugar']);
        });
    }

    /**
     * Give every drink that already has an uploaded image a row in the new
     * images table so the file keeps being served after image_path is gone.
     */
    private function migrateImagePathsToImages(): void
    {
        $now = now();

        DB::table('drinks')
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->each(function (object $drink) use ($now) {
                $imageId = DB::table('images')->insertGetId([
                    'path' => $drink->image_path,
                    'file_name' => basename($drink->image_path),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('drinks')->where('id', $drink->id)->update(['image_id' => $imageId]);
            });
    }
};
