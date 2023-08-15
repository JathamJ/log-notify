<?php

namespace Jathamj\LogNotify;

use \Exception;


// Curl请求
class Curl
{

    private $ch = false;
    private $time_out = 20;
    private $opt = array(
        \CURLOPT_HEADER => 0,
        \CURLOPT_RETURNTRANSFER => 1,
    );
    private $_opt = array(
        \CURLOPT_HEADER => 0,
        \CURLOPT_RETURNTRANSFER => 1,
    );

    private $headers = array();

    static public function init()
    {
        return new self();
    }

    public function __construct()
    {
        $this->ch = curl_init();
    }

    public function setTimeout($timeout)
    {
        $this->time_out = $timeout;
        return $this;
    }

    public function post($url, $data, $opt = array(), $handel = false)
    {
        if (false === $this->ch) {
            throw new Exception('init curl error', 500);
        }
        $this->setOpt(\CURLOPT_POST, 1);
        $this->setOpt(\CURLOPT_URL, $url);
        $this->setOpt(\CURLOPT_SSL_VERIFYPEER, false);
        $this->setOpt(\CURLOPT_SSL_VERIFYHOST, false);
        if (is_array($data) || is_object($data)) {
            $curlPost = http_build_query($data);
        } else {
            $curlPost = $data;
        }
        $this->setOpt(\CURLOPT_POSTFIELDS, $curlPost);
        return $this->_send($opt, $handel);
    }

    public function upload($url, $data, $opt = array())
    {
        if (false === $this->ch) {
            throw new Exception('init curl error', 500);
        }
        $this->setOpt(\CURLOPT_POST, 1);
        $this->setOpt(\CURLOPT_URL, $url);
        if (!is_array($data)) {
            throw new Exception('curl upload data must a array', 500);
        }
        $this->setOpt(\CURLOPT_POSTFIELDS, $data);
        return $this->_send($opt);
    }

    public function __destruct()
    {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
    }

    public function get($url, $data = '', $opt = array(), $handel = false)
    {
        if (false === $this->ch) {
            throw new Exception('init curl error', 500);
        }

        if (is_array($data) || is_object($data)) {
            $getData = http_build_query($data);
        }
        if (is_string($data)) {
            $getData = $data;
        }

        if (!empty($getData)) {
            $url = $url . '?' . $getData;
        }
        $this->setOpt(\CURLOPT_URL, $url);
        return $this->_send($opt, $handel);
    }

    private function _send($opt, $handel = false)
    {
        $this->setOpt(\CURLOPT_TIMEOUT, $this->time_out);
        if (!empty($this->headers)) {
            $this->setOpt(\CURLOPT_HTTPHEADER, $this->headers);
        }

        foreach ($opt as $key => $value) {
            $this->setOpt($key, $value);
        }
        curl_setopt_array($this->ch, $this->_opt);
        if($handel){
            return array(
                'ch' => $this->ch,
                'opt' => $this->_opt
            );
        }
        ob_start();
        $content = curl_exec($this->ch);
        if (curl_errno($this->ch)) {
            $errInfo = $this->getErrInfo();
            $errInfo['curl_err'] = curl_error($this->ch);
            $content = $errInfo;
        }
        curl_close($this->ch);
        return $content;
    }


    public function setOpt($k, $v)
    {
        $this->_opt[$k] = $v;
        curl_setopt($this->ch, $k, $v);
        return $this;
    }

    public function setHeader($k, $v = null)
    {
        if (is_array($k)) {
            foreach ($k as $key => $value) {
                $this->headers[$key] = $value;
            }
            return $this;
        } else {
            $this->headers[$k] = $v;
        }
        return $this;
    }

    public function getErrInfo()
    {
        return curl_getinfo($this->ch);
    }

}