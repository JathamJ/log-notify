<?php

namespace Jathamj\LogNotify\Notify;

use Jathamj\LogNotify\Notify;

/**
 * Sentry 捕获消息
 */
class Sentry implements Notify
{
    protected $dsn;

    public function __construct($config)
    {
        $this->dsn = $config['dsn'] ?? '';
        \Sentry\init(['dsn' => $this->dsn ]);
    }

    public function text($msg) :bool
    {
        $eventId = \Sentry\captureMessage($msg);
        return !empty(strval($eventId));
    }
}