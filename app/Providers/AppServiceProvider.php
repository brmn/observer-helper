<?php

namespace App\Providers;

use App\Infrastructure\Repos\UserRepoHardcode;
use App\Shared\Services\UserRepo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        UserRepo::class => UserRepoHardcode::class,
    ];

    public function register()
    {
        //
    }

    public function boot()
    {
        //
    }
}
