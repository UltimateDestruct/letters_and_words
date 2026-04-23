<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WordApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $path = storage_path('app/testing-wordlist.txt');
        File::ensureDirectoryExists(storage_path('app'));
        File::put($path, "dog\nalpha\ncat\ncow\nhat\nzebra\n");

        config([
            'words.path' => $path,
            'words.max_length' => 10,
        ]);

        $this->app->forgetInstance(\App\Services\WordRepository::class);
    }

    public function test_returns_words_by_length_sorted(): void
    {
        $response = $this->getJson('/api/words?length=3');

        $response->assertOk()
            ->assertJsonPath('count', 4)
            ->assertJsonPath('first', 'cat')
            ->assertJsonPath('last', 'hat')
            ->assertJsonPath('words', ['cat', 'cow', 'dog', 'hat'])
            ->assertJsonPath('page', 1)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total_pages', 1);
    }

    public function test_rejects_invalid_length(): void
    {
        $this->getJson('/api/words?length=0')->assertStatus(422);
        $this->getJson('/api/words?length=-1')->assertStatus(422);
        $this->getJson('/api/words?length=11')->assertStatus(422);
    }

    public function test_empty_length_returns_zeros(): void
    {
        $response = $this->getJson('/api/words?length=9');

        $response->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonPath('first', null)
            ->assertJsonPath('last', null)
            ->assertJsonPath('words', [])
            ->assertJsonPath('page', 1)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total_pages', 0);
    }

    public function test_paginates_words(): void
    {
        $response = $this->getJson('/api/words?length=3&page=1&per_page=2');

        $response->assertOk()
            ->assertJsonPath('count', 4)
            ->assertJsonPath('first', 'cat')
            ->assertJsonPath('last', 'hat')
            ->assertJsonPath('words', ['cat', 'cow'])
            ->assertJsonPath('page', 1)
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total_pages', 2);

        $this->getJson('/api/words?length=3&page=2&per_page=2')
            ->assertOk()
            ->assertJsonPath('words', ['dog', 'hat']);
    }

    public function test_rejects_invalid_page(): void
    {
        $this->getJson('/api/words?length=3&page=0')->assertStatus(422);
    }

    public function test_rejects_invalid_per_page(): void
    {
        $this->getJson('/api/words?length=3&per_page=0')->assertStatus(422);
        $this->getJson('/api/words?length=3&per_page=201')->assertStatus(422);
    }
}
