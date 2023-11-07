<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensCan([
            'get-info-on-apps' => 'View information on apps',
            'get-info-in-background' => 'Get information while app is in background',
            
            //ADMIN SCOPE
            'get-info' => 'Get information',
            'add-admins' => 'Can add administrators',

        ]);
        //Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
    }
}
