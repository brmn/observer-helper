<?php

namespace App\Providers;

use App\Infrastructure\Repos\UserRepoHardcode;
use App\Infrastructure\Voteabroad\Repos\QuestionsDb;
use App\Shared\Services\UserRepo;
use App\Voteabroad\Services\Repos\Questions;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        UserRepo::class => UserRepoHardcode::class,
        Questions::class => QuestionsDb::class
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
