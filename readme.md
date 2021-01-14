# Laravel onelogin

A Laravel package for allowing onelogin to provide authentication and users to your application. This
library wraps onelogin's [onelogin/php-saml](https://github.com/onelogin/php-saml) library.

Features:
- simplified configuration process
- top level (configurable) `login` and `logout` routes
- support for autologin
- ability to map any User attributes via a login event
- loose SAML workflow for localhost/local environments, strict when in production

# Installation

    composer require ziffmedia/laravel-onelogin

Next, publish the configuration file:

    artisan vendor:publish --provider='ZiffMedia\Laravel\Onelogin\OneloginServiceProvider'

### Note for Laravel 5.7+

If your application uses Laravel 5.7 or greater, please make sure this package is updated to v0.0.7 or greater.

# Configuration & Setup

Go into your onelogin administration screen, search for any application (for example one with "SAML" in
the name).  This is *not* the connector to use, instead in the URL replace the app connector id with `43457`
so that it reads something like `https://<your company>.onelogin.com/apps/new/43457`.  Create an app from this
connector template.

The onelogin tutorial is a great reference at https://developers.onelogin.com/saml/php

Once you have an app in onelogin minimally setup, utilize the App > SSO tab to get the necessary
values to put inside the configuration file. See [./config/onelogin.php](./config/onelogin.php)
for details on which fields are necessary.

## The User Setup

(The following setup assumes your users will be populated by onelogin the first time they
successfully try to log into your application.)

Out the box, this package is designed to work with the typical user schema provided with laravel with
minimal changes.  Typical changes to make look like this:

- remove the `2014_10_12_100000_create_password_resets_table.php` migration file
- remove the `$table->timestamp('email_verified_at')->nullable();` and `$table->string('password');` columns from the `2014_10_12_000000_create_users_table.php` migration

### (Optional) Laravel Nova...

Laravel Nova's default installation adds authentication routes to your application, it is wise to remove them
inside your application's `NovaServiceProvider`:

```php
    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                // ->withAuthenticationRoutes()
                // ->withPasswordResetRoutes()
                ->register();
    }
```

### User Attributes and New User Workflow

By default, the following actions happen on successful login (From the OneloginController):

```php
    protected function resolveUser(array $userAttributes)
    {
        $userClass = config('auth.providers.users.model');

        $user = $userClass::firstOrNew(['email' => $credentials['User.email'][0]]);

        if (isset($credentials['User.FirstName'][0]) && isset($credentials['User.LastName'][0])) {
            $user->name = "{$credentials['User.FirstName'][0]} {$credentials['User.LastName'][0]}";
        }

        $user->save();

        return $user;
    }
```

To customize this experience, create an Event inside your applications `EventServiceProvider`'s boot() method:

```php
    public function boot()
    {
          // assuming: use ZiffMedia\Laravel\Onelogin\Events\OneloginLoginEvent;

          Event::listen(OneloginLoginEvent::class, function (OneloginLoginEvent $event) {
              $user = User::firstOrNew(['email' => $event->userAttributes['User.email'][0]]);

              if (isset($event->userAttributes['User.FirstName'][0]) && isset($event->userAttributes['User.LastName'][0])) {
                  $user->name = "{$event->userAttributes['User.FirstName'][0]} {$event->userAttributes['User.LastName'][0]}";
              }

              // other custom logic here

              $user->save();

              return $user;
          });
    }
```

# Local Users in Development (To Bypass Onelogin)

To shortcut the onelogin SAML flow in development, your app has to be in the `local` environment, then ensure that
`app.debug` is `true`, and finally add the following configuration to your `config/onelogin.php` file:

```php
    'local_user' => [
        'enable' => true,

        'attributes' => [
            'email' => 'developer@example.com',
            'name' => 'Software Developer'
        ]
    ]
```
