<?php

namespace Jathamj\LogNotify\Notify;

use Jathamj\LogNotify\Curl;
use Jathamj\LogNotify\Notify;

/**
 * 钉钉机器人消息发送
 * @doc https://open.dingtalk.com/document/robots/custom-robot-access#title-72m-8ag-pqw
 * @doc https://open.dingtalk.com/document/robots/customize-robot-security-settings
 */
class Dingtalk implements Notify
{
    const HOST = 'https://oapi.dingtalk.com/robot/send?access_token=';
    protected $accessToken;

    protected $secret;

    public function __construct($config)
    {
        $this->accessToken = $config['access_token'] ?? '';
        $this->secret = $config['secret'] ?? '';
    }

    protected function getSign()
    {
        list($s1, $s2) = explode(' ', microtime());

        $timestamp = (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);

        $data = $timestamp . "\n" . $this->secret;

        $signStr = base64_encode(hash_hmac('sha256', $data, $this->secret,true));

        $signStr = utf8_encode(urlencode($signStr));

        return "&timestamp=$timestamp&sign=$signStr";
    }

    protected function request($data)
    {
        $url = self::HOST . $this->accessToken;
        if (!empty($this->secret)) {
            $url .= $this->getSign();
        }
        $data = json_encode($data);
        return Curl::init()->setOpt(\CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'))->post($url, $data);
    }

    public function text($msg): bool
    {
        $data = array(
            'msgtype' => 'text',
            'text' => [
                'content' => $msg,
            ],
            'isAtAll'   => false,
        );
        $ret = $this->request($data);
        if (empty($ret)) {
            return false;
        }
        $ret = json_decode($ret, true);
        $errCode = $ret['errcode'] ?? -1;
        return empty($errCode);
    }
}