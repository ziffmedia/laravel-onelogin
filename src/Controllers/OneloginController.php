<?php

namespace ZiffDavis\Laravel\Onelogin\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use ZiffDavis\Laravel\Onelogin\Events\OneloginLoginEvent;
use ZiffDavis\Laravel\User\Auth\OneLoginEloquentUserProvider;

class OneLoginController extends Controller
{
    use HasRedirector;

    protected $oneLogin;
    protected $userProvider;

    function __construct(Auth $oneLogin)
    {
        $this->oneLogin = $oneLogin;
    }

    public function metadata()
    {
        $settings = $this->oneLogin->getSettings();
        $metadata = $settings->getSPMetadata();

        $errors = $settings->validateMetadata($metadata);

        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                'Invalid SP metadata: ' . implode(', ', $errors),
                Error::METADATA_SP_INVALID
            );
        }

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    public function login(Request $request)
    {
        $redirect = $this->getRedirectUrl($request, true);

        // prevent logged in users from triggering a onelogin saml flow
        if ($request->user()) {
            return redirect($redirect);
        }

        return redirect($this->oneLogin->login($redirect, [], false, false, true));
    }

    public function acs(Request $request, AuthManager $auth)
    {
        $this->oneLogin->processResponse();
        $errors = $this->oneLogin->getErrors();

        if (!empty($errors)) {
            $errorString = implode(', ', $errors);
            if ($errorReason = $this->oneLogin->getLastErrorReason()) {
                $errorString .= ' Error Reason: ' . $errorReason;
            }
            throw new \RuntimeException($errorString);
        }

        abort_if(!$this->oneLogin->isAuthenticated(), 403, 'Unauthorized to use this application');

        $userAttributes = $this->oneLogin->getAttributes();

        $loginEvent = new OneloginLoginEvent($userAttributes);
        $results = Event::fire($loginEvent);

        $user = array_first($results, function ($result) {
            return $result instanceof Authenticatable;
        });

        abort_if(array_search(false, $results, true) !== false, 403, 'There is no valid user in this application for provided credentials');

        // if there was no Event fired, do the default action
        if (!$user && count($results) === 0) {
            $user = $this->resolveUser($userAttributes);
        }

        abort_if(!$user, 500, 'A user could not be resolved by the Onelogin Controller');

        $auth->login($user);

        return redirect($request->get('RelayState') ?? '/');
    }

    protected function resolveUser(array $credentials)
    {
        $userClass = config('auth.providers.users.model');

        $user = $userClass::firstOrNew(['email' => $credentials['User.email'][0]]);

        if (isset($credentials['User.FirstName'][0]) && isset($credentials['User.LastName'][0])) {
            $user->name = "{$credentials['User.FirstName'][0]} {$credentials['User.LastName'][0]}";
        }

        $user->save();

        return $user;
    }
}


