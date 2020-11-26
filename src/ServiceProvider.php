<?php

declare(strict_types=1);

namespace LaravelJsonApi\Spec;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            JsonApiSpec::$translationNamespace
        );

        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/' . JsonApiSpec::$translationNamespace),
        ]);
    }

    /**
     * Bind package services into the service container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(Specification::class, static function (Application $app) {
            return $app->make(JsonApiSpec::$specification);
        });

        $this->app->singleton(Translator::class);
    }
}
