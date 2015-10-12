<?php
namespace Wisembly\Behat\Extension\Context;

use RuntimeException;

class ProfileNotFoundException extends RuntimeException
{
    public function __construct($token)
    {
        parent::__construct("Profile $token not found");
    }
}

