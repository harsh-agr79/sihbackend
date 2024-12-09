<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\Student;
use App\Models\Mentor;
use App\Models\Company;
use App\Models\Teacher;
use App\Models\Institute;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model-to-policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Map your models to their policies here if needed
        // Example: 'App\Models\SomeModel' => 'App\Policies\SomeModelPolicy',
    ];

    /**
     * Register any authentication or authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Bind Sanctum to use the Student model for personal access tokens
        Sanctum::usePersonalAccessTokenModel(Student::class);
        Sanctum::usePersonalAccessTokenModel(Mentor::class);
        Sanctum::usePersonalAccessTokenModel(Company::class);
        Sanctum::usePersonalAccessTokenModel(Teacher::class);
        Sanctum::usePersonalAccessTokenModel(Institute::class);
        Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);
    }
}
