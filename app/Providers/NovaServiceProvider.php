<?php

namespace App\Providers;

use Laravel\Nova\Nova;
use Laravel\Nova\Cards\Help;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            // new Help,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    protected function resources()
    {
        Nova::resources([
            \App\Nova\User::class,
            \App\Nova\UserSetting::class,
            \App\Nova\Balance::class,
            \App\Nova\TutorProfile::class,
            \App\Nova\ParentProfile::class,
            \App\Nova\StudentProfile::class,
            \App\Nova\VideoClass::class,
            \App\Nova\Address::class,
            \App\Nova\City::class,
            \App\Nova\Country::class,
            \App\Nova\BasicDegree::class,
            \App\Nova\Degree::class,
            \App\Nova\Identity::class,
            \App\Nova\Minor::class,
            \App\Nova\School::class,
            \App\Nova\SchoolLevel::class,
            \App\Nova\State::class,
            \App\Nova\Topic::class,
            \App\Nova\Price::class,
            \App\Nova\W9Form::class,
            \App\Nova\CreditCardInfo::class,
            \App\Nova\PaypalInfo::class,
            \App\Nova\Feedback::class,
            \App\Nova\Transaction::class,
            \App\Nova\TransactionType::class,
        ]);
    }
}
