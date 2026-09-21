<?php

namespace Tests\Feature\Api\V3;

use App\Models\Image;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_advertises_image_support(): void
    {
        $this->getJson('/v3/images/')->assertNoContent();
    }

    public function test_it_stores_an_uploaded_image(): void
    {
        Storage::fake('public');

        $response = $this->post('/v3/images/', ['image' => UploadedFile::fake()->image('mate.png')], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.file_name', 'mate.png');

        $image = Image::firstOrFail();
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_it_rejects_an_unsupported_media_type(): void
    {
        Storage::fake('public');

        $this->post('/v3/images/', ['image' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain')], [
            'Accept' => 'application/json',
        ])->assertStatus(415);

        $this->assertDatabaseCount('images', 0);
    }

    public function test_it_rejects_an_oversized_image(): void
    {
        Storage::fake('public');
        config(['spacemarket.max_image_kilobytes' => 10]);

        $this->post('/v3/images/', ['image' => UploadedFile::fake()->image('huge.png')->size(50)], [
            'Accept' => 'application/json',
        ])->assertStatus(413);
    }

    public function test_it_returns_image_metadata(): void
    {
        $image = Image::factory()->create(['file_name' => 'mate.png']);

        $this->getJson("/v3/images/{$image->id}")
            ->assertOk()
            ->assertJsonPath('0.id', $image->id)
            ->assertJsonPath('0.file_name', 'mate.png');
    }

    public function test_it_serves_the_image_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/mate.png', 'png-bytes');
        $image = Image::factory()->create(['path' => 'images/mate.png']);

        $this->get("/v3/images/{$image->id}/img")->assertOk();
    }

    public function test_a_missing_file_is_not_found(): void
    {
        Storage::fake('public');
        $image = Image::factory()->create();

        $this->getJson("/v3/images/{$image->id}/img")->assertNotFound();
    }
}
