<?php

return [
    /**
     * Taken from your apps SSO configuration screen, the field called "Issuer URL"
     */
    'issuer_url' => 'https://app.onelogin.com/saml/metadata/...',

    /**
     * Taken from your apps SSO configuration screen, the field called "SAML 2.0 Endpoint (HTTP)",
     * this is your "single sign on url"
     */
    'sso_url' => 'https://yourdomain.onelogin.com/trust/saml2/http-post/sso/...',

    /**
     * Taken from your apps SSO configuration screen, the field called "SLO Endpoint (HTTP)"
     */
    'slo_url' => 'https://yourdomain.onelogin.com/trust/saml2/http-redirect/slo/...',

    /**
     * Taken from your apps SSO configuration screen, to get this value, click on "View Details"
     * of the X.509 certificate on the SSO page.  Once you see the certificate, paste its value
     * (with or without newlines) inside the quoted value below. (This will be the textarea where
     * the contents start with -----BEGIN CERTIFICATE-----
     */
    'x509_cert' => 'MII......=',

    /**
     * These values affect how the appliaction behaves with regards to setting up urls and redirecting
     */
    'routing' => [

        /**
         * By default, use the 'web' middleware for the onelogin.* route group, as well as the
         * root routes /login and /logout if they are enabled
         */
        'middleware' => 'web',

        /**
         * The domain to attach just the onelogin.* routes to
         */
        'domain' => null,

        /**
         * The url that will be used when no "redirect back"/"previous" url can be determined in
         * a workflow
         */
        'fallback_redirect' => '/',

        /**
         * This plugin can provide /login and /logout routes to your application if they are enabled (which
         * they are by default).  Do this instead of using `artisan make:auth`
         */
        'root_routes' => [

            /**
             * enable?
             */
            'enable' => true,

            /**
             * Autologin (with enabled root routes) will not present a login button on the /login screen,
             * instead it will automatically redirect to the onelogin.login route. The actual behavior here
             * is that when a ->middleware('auth') route is hit by an unauthenticated user, the Error/Exception
             * handler will attempt to redirect to /auth, which the laravel-onelogin package can now handle for you.
             */
            'autologin' => false,
        ],

        /**
         * In certain circumstances (such as using cloudflare edge auth), the initial ACS POST request is
         * inadvertantly turned into a GET request to the ACS route. Enabling this will make sure that GET
         * requests are also redirected back to the onelogin SAML flow
         */
        'enable_acs_redirect_for_get' => false,
    ],

    /**
     * By default, the onelogin package will use the auth.defaults.guard as the guard to setup the user.
     * For applications with multiple guards (admin users vs. site users), configure this to use the guard
     * for the set of users you with to authenticate against one login.
     *
     * Note: the guard's provider must have a auth.providers.{provider}.model option
     */
    'guard' => null
];
