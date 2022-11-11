<?php

namespace Ja\Notion\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    private string $path = __DIR__ . '/../..';

    public function register()
    {
        //
    }

    public function boot()
    {
        // $this
        //   ->loadViews()
        //   ->loadConfig()
        //   ->loadTranslations();
    }

    private function loadViews(): self
    {
        $this->loadViewsFrom(
            "{$this->path}/resources/views",
            'blazervel-ui'
        );

        return $this;
    }

    private function loadConfig()
    {
        $this->publishes([
            "{$this->path}/config/blazervel/ui.php" => config_path('blazervel/ui.php'),
        ], 'blazervel');

        return $this;
    }

    private function loadTranslations(): self
    {
        $this->loadTranslationsFrom(
            "{$this->path}/lang",
            'blazervel_ui'
        );

        return $this;
    }
}
