<?php

namespace ZiffMedia\LaravelOnelogin\Events;

class OneloginLoginEvent
{
    /** @var array */
    public $userAttributes = [];

    public function __construct(array $userAttributes)
    {
        $this->userAttributes = $userAttributes;
    }
}
