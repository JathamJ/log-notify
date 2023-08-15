<?php

namespace Jathamj\LogNotify\Notify;

use Jathamj\LogNotify\Notify;

class Qywx implements Notify
{
    public function __construct($config)
    {

    }

    public function text($msg, $at = []): bool
    {
        return true;
    }

}