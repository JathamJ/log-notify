# log-notify
一个记录日志时同时发送消息通知的插件包，支持配置报警策略以及多种通知方式。

### 安装
```shell
composer require jathamj/log-notify
```

### 使用
建议集成到日志包中

```php

$handler = new \Jathamj\LogNotify\Handler($config);

$handler->do('ERROR', '订单中心', ['orderId' => 2323, 'sd' => 'sadasd']);

```

### 配置参考

```php
return [

    //通知接口
    'api'    => array(
        'default'   => [
            'type'              => 'dingtalk',  //通知接口类型（钉钉）
            'access_token'      => '983d92ec8a7594b927babe9fdbbad116871828f538cb18ab5ebe742547e241c6',
            'secret'            => 'SECc2e0ff9e530b5f2666a023be29f289f146f91fdfe0207e5e3d0e883e11951cf7',
        ],
    ),

    //缓存
    'redis'     => [
        'host'  => '127.0.0.1',
        'port'  => 6379,
    ],

    //报警模块
    'modules'    => array(
        'default'   => [
            'api'               => 'default',    //通知接口 (对应api配置)
            'label'             => '订单中心',    //客户化名称
            'interval'          => 3600,        //统计周期
            'times'             => 3,           //warning触发报警次数
            'frequency'         => 600,         //warning报警间隔
            'error.frequency'   => 1,           //error报警间隔
        ],
    ),
];
```

