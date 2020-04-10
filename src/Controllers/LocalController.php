<?php

namespace ZiffMedia\LaravelOnelogin\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LocalController extends Controller
{
    use HasRedirector;

    public function login(Request $request)
    {
        $redirect = $this->getRedirectUrl($request, true);

        if ($request->user()) {
            return redirect($redirect);
        }

        $oneloginLoginUrl = route('onelogin.login', compact('redirect'));

        if (config('onelogin.routing.root_routes.autologin', false) === true) {
            return redirect($oneloginLoginUrl);
        }

        return view('onelogin::login', compact('oneloginLoginUrl'));
    }

    public function logout(Request $request, AuthManager $auth)
    {
        if ($request->user()) {
            $auth->logout();
            $message = "You've successfully logged out.";
        } else {
            $message = 'You are not currently logged in.';
        }

        $redirect = $this->getRedirectUrl($request, true);
        $oneloginLoginUrl = route('onelogin.login', compact('redirect'));

        return view('onelogin::logout', compact('message', 'oneloginLoginUrl'));
    }
}


