<?php

namespace Jathamj\LogNotify\Notify;

use Jathamj\LogNotify\Curl;
use Jathamj\LogNotify\Notify;

/**
 * 企业微信消息发送
 * @doc https://developer.work.weixin.qq.com/document/path/99110
 */
class Qywx implements Notify
{
    const HOST = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=';

    protected $accessToken;

    public function __construct($config)
    {
        $this->accessToken = $config['access_token'] ?? '';
    }

    protected function request($data)
    {
        $url = self::HOST . $this->accessToken;
        $data = json_encode($data);
        return Curl::init()->setOpt(\CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'))->post($url, $data);
    }

    public function text($msg): bool
    {
        $data = [
            'msgtype'   => 'text',
            'text'      => [
                'content'   => $msg,
            ],
        ];
        $ret = $this->request($data);
        $ret = json_decode($ret, true);
        $errCode = $ret['errcode'] ?? -1;
        return empty($errCode);
    }

}