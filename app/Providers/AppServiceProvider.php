<?php

namespace App\Providers;

use App\Services\WordRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WordRepository::class, function (): WordRepository {
            return new WordRepository(
                config('words.path'),
                (int) config('words.max_length'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
