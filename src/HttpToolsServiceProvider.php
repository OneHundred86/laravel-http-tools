<?php

namespace Oh86\Http;

use Illuminate\Support\ServiceProvider;

class HttpToolsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/errorcode.php' => config_path('errorcode.php'),
        ]);
    }
}