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

    composer require ziffdavis/laravel-onelogin

Next, publish the configuration file:

    artisan vendor:publish --provider=ZiffDavis\Laravel\Onelogin\OneloginServiceProvider

# Configuration

Go into your onelogin administration screen, create an application with the
"SAML Test Connector (IdP w/attr)" template.  The onelogin tutorial is a great reference at
https://developers.onelogin.com/saml/php

Once you have an app in onelogin minimally setup, utilize the App > SSO tab to get the necessary
values to put inside the configuration file.
