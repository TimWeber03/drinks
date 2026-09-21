<?php

namespace App\Models;

use Database\Factories\ImageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    /** @use HasFactory<ImageFactory> */
    use HasFactory;

    protected $fillable = [
        'path',
        'file_name',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * Store an uploaded file on the public disk and record it as an image.
     */
    public static function storeUploadedFile(UploadedFile $file, string $directory): self
    {
        return self::create([
            'path' => $file->store($directory, 'public'),
            'file_name' => $file->getClientOriginalName() ?: basename($file->hashName()),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Publicly reachable URL of the stored file.
     */
    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Remove the file from disk along with the record.
     */
    public function deleteWithFile(): void
    {
        Storage::disk('public')->delete($this->path);

        $this->delete();
    }
}
