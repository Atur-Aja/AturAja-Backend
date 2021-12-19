<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

//        VerifyEmail::toMailUsing(function ($notifiable, $url) {
////            $url = URL::temporarySignedRoute(
////                'verification.verify',
////                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
////                [
////                    'id' => $notifiable->getKey(),
////                    'hash' => sha1($notifiable->getEmailForVerification()),
////                ]
////            );
//            $url= 'http://localhost:3000/login';
//            return (new MailMessage)
//                ->subject('Verify Email Address')
//                ->line('Click the button below to verify your email address.')
//                ->action('Verify Email Address', $url)
//                ->line('Thank you for using Atur Aja');
//        });
    }
}
