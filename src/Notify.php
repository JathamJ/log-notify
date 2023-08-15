<?php

namespace Jathamj\LogNotify;

use \Exception;

interface Notify {
    public function text($msg, $at = []) :bool;
}