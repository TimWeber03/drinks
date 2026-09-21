<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('mime_type')->nullable()->after('file_name');
            $table->unsignedInteger('size')->nullable()->after('mime_type');
        });

        $this->backfillFromDisk();
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['mime_type', 'size']);
        });
    }

    /**
     * The v1 API reports the mime type and byte size of a drink logo, which
     * older uploads only know from the file itself.
     */
    private function backfillFromDisk(): void
    {
        $disk = Storage::disk('public');

        DB::table('images')->orderBy('id')->each(function (object $image) use ($disk) {
            if (! $disk->exists($image->path)) {
                return;
            }

            DB::table('images')->where('id', $image->id)->update([
                'mime_type' => $disk->mimeType($image->path) ?: null,
                'size' => $disk->size($image->path),
            ]);
        });
    }
};
