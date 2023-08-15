<?php

namespace Jathamj\LogNotify;

use Jathamj\LogNotify\Notify\Dingtalk;
use Jathamj\LogNotify\Notify\Qywx;

class Handler
{
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';

    const NOTIFY_DING_TALK = 'dingtalk';
    const NOTIDY_QY_WEIXIN = 'qywx';

    const DEFAULT_API = 'default';   //默认报警模块配置

    const DEFAULT_INTERVAL = 3600;  //默认统计周期(单位：秒)
    const DEFAULT_WARNING_TIMES     = 3;    //默认waning频次（单位：次） ps.默认1小时内发生3次相同warning msg即报警

    const DEFAULT_WARNING_FREQUENCY = 300;  //默认warning报警频率(单位：秒) ps.300秒内仅提醒一次

    const DEFAULT_ERROR_FREQUENCY = 60;     //默认error报警频率（单位：秒）

    const TIMES_KEY = 'jathamj_log_notify_zset_%s_%s_%s';    //报警次数key

    const HAS_SEND_KEY = 'jathamj_log_send_%s_%s_%s';  //发送报警key


    /**
     * @var \Redis
     */
    protected $redis;

    protected $modules = [];

    protected $notifyConfigs = [];

    protected $notifyInstances = [];


    public function __construct($config)
    {
        //初始化redis
        if (!empty($config['redis'])) {
            $this->redis = $this->initRedis($config['redis']);
        } else {
            if (!empty($config['interval'])) {
                throw new \Exception('Please config redis to open warning interval notify.');
            }
        }

        if (empty($config['api'])) {
            throw new \Exception('api config empty.');
        }

        //通知配置
        $this->notifyConfigs = $config['api'];

        //模块报警配置
        $this->modules = $config['modules'] ?? [];
    }

    /**
     * 初始化redis连接
     * @param array $cfg 配置信息 例： ['host' => '127.0.0.1', 'port' => 6374, 'password' => '', 'db' => 0]
     * @return \Redis
     * @throws \Exception
     */
    protected function initRedis($cfg)
    {
        try {
            $redis = new \Redis();
            $redis->connect($cfg['host'], intval($cfg['port']), 500);
            if (!empty($cfg['password'])) {
                $redis->auth($cfg['password']);
            }
            if(!empty($cfg['db'])) {
                $redis->select($cfg['db']);
            }
            return $redis;
        } catch (\RedisException $e) {
            throw new \Exception('connect redis error', 500);
        }
    }

    /**
     * 获取通知对象
     * @param $module
     * @return Notify
     * @throws \Exception
     */
    protected function getNotifyInstance($module)
    {
        $api = $this->modules[$module]['api'] ?? self::DEFAULT_API;

        if (isset($this->notifyInstances[$api])) {
            return $this->notifyInstances[$api];
        }

        if (!isset($this->notifyConfigs[$api])) {
            throw new \Exception(sprintf('api[%s] notify config not exist.', $api));
        }

        $cfg = $this->notifyConfigs[$api];
        switch ($cfg['type']) {
            case self::NOTIFY_DING_TALK:
                $this->notifyInstances[$api] = new Dingtalk($cfg);
                break;
            case self::NOTIDY_QY_WEIXIN:
                $this->notifyInstances[$api] = new Qywx($cfg);
                break;
            default:
                throw new \Exception('not support notify type: ' . $cfg['type']);
        }
        return $this->notifyInstances[$api];
    }

    /**
     * 处理错误报警
     * @param string $level 错误级别 WARNING、ERROR
     * @param string $msg 错误信息
     * @param array $params 参数
     * @param string $module 报警模块
     * @return true
     * @throws \RedisException
     */
    public function do($level, $msg, $params = [], $module = self::DEFAULT_API)
    {
        if (!in_array($level, [self::LEVEL_WARNING, self::LEVEL_ERROR])) {
            return true;
        }
        if (!isset($this->modules[$module])) {
            $module = self::DEFAULT_API;
        }
        $notifyInstance = $this->getNotifyInstance($module);
        $moduleText = !empty($this->modules[$module]['label']) ? $this->modules[$module]['label'] : $module;
        $msgHash = sha1($msg);

        //统计周期
        $interval = $this->modules[$module]['interval'] ?? 0;
        if (empty($interval)) {
            $interval = self::DEFAULT_INTERVAL;
        }

        //发生次数
        $times = $this->modules[$module]['times'] ?? 0;
        if (empty($times)) {
            $times = self::DEFAULT_WARNING_TIMES;
        }

        //报警频次
        $frequency = $this->modules[$module]['frequency'] ?? 0;
        if (empty($frequency)) {
            $frequency = self::DEFAULT_WARNING_FREQUENCY;
        }

        //error报警频次
        if ($level == self::LEVEL_ERROR) {
            $frequency = $this->modules[$module]['error.frequency'] ?? 0;
            if (empty($frequency)) {
                $frequency = self::DEFAULT_ERROR_FREQUENCY;
            }
        }

        $at = $this->modules[$module]['at'] ?? '';
        if (!empty($at)) {
            $at = explode(',', $at);
        }

        //redisKey
        //发送过报警
        $hasSend = sprintf(self::HAS_SEND_KEY, $level, $module, $msgHash);
        //发生次数
        $timesKey = sprintf(self::TIMES_KEY, $level, $module, $msgHash);

        //记录发生次数
        $now = time();
        $this->redis->zAdd($timesKey, microtime(true), $now);
        $this->redis->expire($timesKey, $interval);

        //判断是否发送过报警
        if ($this->redis->exists($hasSend)) {
            return true;
        }

        //统计次数
        $times = $this->redis->zCount($timesKey, $now - $interval, $now);

        //发送报警
        $send = $notifyInstance->text(sprintf("【%s】[%s]%s\n%s内已发生%d次\n参数：\n%s", $level, $moduleText, $msg, Utils::s2text($interval), $times, json_encode($params)), $at);

        if ($send) {
            //记录发送过
            $this->redis->setex($hasSend, $frequency, 1);
        }
        return true;
    }

}