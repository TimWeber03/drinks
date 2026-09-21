<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drinkers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->boolean('audit')->default(false)->after('active');
            $table->boolean('redirect')->default(true)->after('audit');
            $table->foreignId('avatar_id')->nullable()->after('redirect')->constrained('images')->nullOnDelete();
        });

        $this->migrateAvatarPathsToImages();

        Schema::table('drinkers', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('drinkers', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('name');
        });

        DB::table('drinkers')
            ->join('images', 'images.id', '=', 'drinkers.avatar_id')
            ->update(['drinkers.avatar_path' => DB::raw('images.path')]);

        Schema::table('drinkers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('avatar_id');
            $table->dropColumn(['email', 'audit', 'redirect']);
        });
    }

    /**
     * Keep already uploaded avatars reachable once avatar_path is replaced by
     * a reference into the images table.
     */
    private function migrateAvatarPathsToImages(): void
    {
        $now = now();

        DB::table('drinkers')
            ->whereNotNull('avatar_path')
            ->orderBy('id')
            ->each(function (object $drinker) use ($now) {
                $imageId = DB::table('images')->insertGetId([
                    'path' => $drinker->avatar_path,
                    'file_name' => basename($drinker->avatar_path),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('drinkers')->where('id', $drinker->id)->update(['avatar_id' => $imageId]);
            });
    }
};
