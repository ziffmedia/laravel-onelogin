<?php

namespace ZiffMedia\LaravelOnelogin;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use OneLogin\Saml2;

class OneloginServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(Router $router)
    {
        $configSourcePath = realpath(__DIR__ . '/../config/onelogin.php');

        $router->middlewareGroup('onelogin', [Middleware\OneloginCsrfDisablerMiddleware::class]);

        $middlewares = Arr::wrap(config('onelogin.routing.middleware'));

        $routeGroupParams = [
            'namespace' => 'ZiffMedia\LaravelOnelogin\Controllers',
            'as' => 'onelogin.',
            'prefix' => 'onelogin/',
            'middleware' => array_merge(['onelogin'], $middlewares),
        ];

        // @todo implement SSO routes at /logout
        $router->group($routeGroupParams, function () use ($router) {
            $router->get('/metadata', 'OneloginController@metadata')->name('metadata');
            $router->get('/login', 'OneloginController@login')->name('login');
            $router->match(['get', 'post'], '/acs', 'OneloginController@acs')->name('acs');
        });

        if (config('onelogin.routing.root_routes.enable')) {
            $rootRouteGroupParams = ['namespace' => 'ZiffMedia\LaravelOnelogin\Controllers', 'middleware' => $middlewares];

            $router->group($rootRouteGroupParams, function () use ($router) {
                $router->get('login', 'LocalController@login')->name('login');
                $router->get('logout', 'LocalController@logout')->name('logout');
            });
        }

        // console setup
        if ($this->app->runningInConsole()) {
            // dev time only tasks
            if ($this->app->environment('local')) {
                $this->publishes([
                    $configSourcePath => config_path('onelogin.php'),
                ]);
            }
        }

        $this->mergeConfigFrom($configSourcePath, 'onelogin');

        $this->loadViewsFrom(__DIR__ . '/../views', 'onelogin');
    }

    public function register()
    {
        $this->app->singleton(Saml2\Auth::class, function () {
            if (request()->isSecure()) {
                Saml2\Utils::setSelfProtocol('https');
            }

            return new Saml2\Auth([
                'strict' => app()->environment('production'),
                'debug' => config('app.debug', false),
                'baseurl' => null,
                'sp' => [
                    'entityId' => route('onelogin.metadata'),
                    'assertionConsumerService' => [
                        'url' => route('onelogin.acs'),
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    ],
                    'attributeConsumingService' => [
                        'ServiceName' => 'SP test',
                        'serviceDescription' => 'Test Service',
                        'requestedAttributes' => [
                            [
                                'name' => '',
                                'isRequired' => false,
                                'nameFormat' => '',
                                'friendlyName' => '',
                                'attributeValue' => ''
                            ]
                        ]
                    ],
                    'singleLogoutService' => [
                        'url' => route('onelogin.acs'),
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
                    'x509cert' => '',
                    'privateKey' => '',
                ],
                'idp' => [
                    'entityId' => config('onelogin.issuer_url'),
                    'singleSignOnService' => [
                        'url' => config('onelogin.sso_url'),
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'singleLogoutService' => [
                        'url' => config('onelogin.slo_url'),
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ],
                    'x509cert' => config('onelogin.x509_cert')
                ]
            ]);
        });
    }
}
