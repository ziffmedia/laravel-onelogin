<?php

namespace ZiffMedia\Laravel\Onelogin\Events;

class OneloginLoginEvent
{
    /** @var array */
    public $userAttributes = [];

    public function __construct(array $userAttributes)
    {
        $this->userAttributes = $userAttributes;
    }
}
