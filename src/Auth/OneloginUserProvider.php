<?php

namespace ZiffDavis\Laravel\Onelogin\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Application;

class OneloginUserProvider
{
    protected $app;
    protected $config;
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $userClass = $this->createModel();
        return $userClass::find($identifier);
    }
    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return false;
    }
    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // do nothing, remember tokens not supported
    }
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (isset($credentials['User.email'])) {
            // create new users via the credentials provided (these will be onelogin ACS attributes)
            $userClass = $this->createModel();
            $user = $userClass::firstOrNew(['email' => $credentials['User.email'][0]]);
            $user->type = 'human';
            $user->name = "{$credentials['User.FirstName'][0]} {$credentials['User.LastName'][0]}";
            // the "roles" column will not be updated unless OneLogin is providing that mapping
            if ($parameterMetadataMap = config('zd_user.onelogin_parameter_metadata_map')) {
                $metadata = $user->metadata;
                foreach ($parameterMetadataMap as $oneloginParameter => $metadataName) {
                    if (!isset($credentials[$oneloginParameter][0])) {
                        continue;
                    }
                    $metadata[$metadataName] = $credentials[$oneloginParameter][0];
                }
                $user->metadata = $metadata;
            }
            $user->save();
            return $user;
        }
    }
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // always true, only way users get into the system is via retrieveByCredentials(), via attempt()
        return true;
    }
    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->config['model'], '\\');
        return new $class;
    }
}