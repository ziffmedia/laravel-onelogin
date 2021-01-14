<?php

namespace ZiffMedia\LaravelOnelogin\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\ValidationError;
use ZiffMedia\LaravelOnelogin\Events\OneloginLoginEvent;

class OneloginController extends Controller
{
    use HasRedirector;

    /** @var Auth */
    protected $oneLogin;

    /** @var string The guard to use */
    protected $guard;

    function __construct(Auth $oneLogin)
    {
        $this->oneLogin = $oneLogin;
        $this->guard = config('onelogin.guard') ?? config('auth.defaults.guard');
    }

    public function metadata()
    {
        $settings = $this->oneLogin->getSettings();

        $metadata = '';

        try {
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);
        } catch (\Exception $e) {
            $errors = [$e->getMessage()];
        }

        abort_if(!empty($errors), 500, 'Onelogin metadata errors: ' . implode(',', $errors));

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    public function login(Request $request, AuthManager $auth)
    {
        $redirect = $this->getRedirectUrl($request, true);

        if (app()->isLocal() && config('app.debug') && config('onelogin.local_user.enable')) {
            $user = $this->resolveLocalUser();

            $auth->guard($this->guard)->login($user);

            return redirect($redirect);
        }

        // prevent logged in users from triggering a onelogin saml flow
        if ($request->user($this->guard)) {
            return redirect($redirect);
        }

        $url = null;

        try {
            $url = $this->oneLogin->login($redirect, [], false, false, true);
        } catch (Error $errorException) {
            abort(500, 'Onelogin URL Generation error: ' . $errorException->getMessage());
        }

        return redirect($url);
    }

    public function acs(Request $request, AuthManager $auth)
    {
        /**
         * Support GET requests only when configured to respond, in those cases redirect to onelogin
         */
        if ($request->isMethod('GET')) {
            abort_if(!config('onelogin.routing.enable_acs_redirect_for_get', false), 405);

            return redirect(
                $this->oneLogin->login($this->getRedirectUrl($request), [], false, false, true)
            );
        }

        try {
            $this->oneLogin->processResponse();

            $error = $this->oneLogin->getLastErrorReason();
        } catch (ValidationError | Error $errorException) {
            $error = $errorException->getMessage();
        }

        abort_if(!empty($error), 500, 'Onelogin ACS validation errors: ' . $error);

        abort_if(!$this->oneLogin->isAuthenticated(), 403, 'Unauthorized to use this application');

        $userAttributes = $this->oneLogin->getAttributes();

        $loginEvent = new OneloginLoginEvent($userAttributes);
        $results = Event::dispatch($loginEvent);

        $user = Arr::first($results, function ($result) {
            return $result instanceof Authenticatable;
        });

        abort_if(array_search(false, $results, true) !== false, 403, 'There is no valid user in this application for provided credentials');

        // if there is no User (and a listener did not return false)
        if (!$user) {
            $user = $this->resolveUser($userAttributes);
        }

        abort_if(!$user, 500, 'A user could not be resolved by the Onelogin Controller');

        $auth->guard($this->guard)->login($user);

        return redirect($request->get('RelayState') ?? '/');
    }

    protected function resolveUser(array $userAttributes)
    {
        $userClass = $this->getUserClass();

        /** @var User $user */
        $user = $userClass::firstOrNew(['email' => $userAttributes['User.email'][0]]);

        if (isset($userAttributes['User.FirstName'][0]) && isset($userAttributes['User.LastName'][0])) {
            $user->name = "{$userAttributes['User.FirstName'][0]} {$userAttributes['User.LastName'][0]}";
        }

        $user->save();

        return $user;
    }

    protected function resolveLocalUser()
    {
        $userClass = $this->getUserClass();

        $userAttributes = config('onelogin.local_user.attributes');

        abort_if(! isset($userAttributes['email']), 500, 'Your configuration is using onelogin.local_user, but there is no onelogin.local_user.attributes.email defined.');

        return tap($userClass::firstOrNew(['email' => $userAttributes['email']]), function ($user) use ($userAttributes) {
            if (isset($userAttributes['name'])) {
                $user->name = $userAttributes['name'];
            }

            $user->save();
        });
    }

    protected function getUserClass()
    {
        $guardProvider = config('auth.guards.' . $this->guard . '.provider');

        abort_if(!$guardProvider, 500, 'The guard auth.guards.' . $this->guard . ' is not configured properly.');

        /** @var User $userClass */
        $userClass = config("auth.providers.{$guardProvider}.model");

        abort_if(!$userClass || !class_exists($userClass), 500, 'A user class was not configured to be used by the laravel-onelogin controller');

        return $userClass;
    }
}
