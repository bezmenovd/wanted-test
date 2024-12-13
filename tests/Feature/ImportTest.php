<?php

namespace Tests\Feature;

use App\Jobs\ImportJob;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;

class ImportTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_valid(): void
    {
        Queue::fake();

        $response = $this->withHeaders([
            'Authorization' => sprintf("Basic %s", base64_encode("email@example.com:password")),
            'Accept' => 'application/json',
        ])->post('/api/import', [
            'file' => new UploadedFile(
                Storage::disk('tests')->path('Backend developer файл для импорта 2024-05-29.xlsx'), 
                'Backend developer файл для импорта 2024-05-29.xlsx',
            ),
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(ImportJob::class);
    }
}
