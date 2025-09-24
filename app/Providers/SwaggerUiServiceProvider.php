<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SwaggerUiServiceProvider extends ServiceProvider
{
    public function boot() : void
    {
        Gate::define('viewSwaggerUI', function ($user = null) {
            // Allow access for authenticated users
            // For HMAC API testing, we'll configure middleware differently
            return $user !== null;
        });
    }
}
