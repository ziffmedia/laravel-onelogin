<?php

namespace ZiffDavis\Laravel\Onelogin\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class OneloginLoginEvent
{
    /** @var array */
    public $userAttributes = [];
    public function __construct(array $userAttributes)
    {
        $this->userAttributes = $userAttributes;
    }
}