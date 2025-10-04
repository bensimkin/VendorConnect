<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use SendGrid\Mail\Mail as SendGridMail;
use SendGrid;

class SendGridServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('sendgrid', function ($app) {
            return new SendGrid($app['config']['mail.mailers.sendgrid.api_key']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Mail::extend('sendgrid', function (array $config) {
            return new \App\Mail\SendGridTransport(
                new SendGrid($config['api_key'])
            );
        });
    }
}
