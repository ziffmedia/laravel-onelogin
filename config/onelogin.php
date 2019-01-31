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
    'sso_url' => 'https://zdcommerce.onelogin.com/trust/saml2/http-post/sso/...',

    /**
     * Taken from your apps SSO configuration screen, the field called "SLO Endpoint (HTTP)"
     */
    'slo_url' => 'https://zdcommerce.onelogin.com/trust/saml2/http-redirect/slo/...',

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
        ]
    ]
];
