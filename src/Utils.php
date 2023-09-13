<?php

namespace Jathamj\LogNotify;

class Utils
{
    /**
     * 秒转文字
     * @param $second
     * @return string
     */
    public static function s2text($second)
    {
        if ($second < 60) {
            return $second . '秒';
        }
        if ($second < 3600) {
            return intval($second / 60) . '分钟';
        }
        return intval($second / 3600) . '小时';
    }

    //获取服务器名称
    public static function getHostName()
    {
        return $_SERVER['HOSTNAME'] ?? '';
    }

    //获取服务器ip
    public static function getRemoteIp()
    {
        return $_SERVER['SERVER_ADDR'] ?? '';
    }
}