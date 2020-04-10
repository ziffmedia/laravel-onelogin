<?php

namespace ZiffMedia\LaravelOnelogin\Controllers;

use Illuminate\Http\Request;

trait HasRedirector
{
    protected function getRedirectUrl(Request $request, $considerPrevious = false)
    {
        // first priority is to use a redirect url
        if ($request->query->has('redirect')) {
            return $request->query->get('redirect');
        }

        // next, use a previous url if it is not this url
        if ($considerPrevious && url()->current() !== ($previous = url()->previous())) {
            if (strpos($previous, $request->root()) !== false) {
                return $previous;
            }
        }

        // finally, use a configured landing route, or just use /
        $fallbackRedirect = config('onelogin.routing.fallback_redirect');

        // if the thing is NOT a url
        if (strpos($fallbackRedirect, '/') === false) {
            return route($fallbackRedirect);
        }

        return $fallbackRedirect;
    }
}
