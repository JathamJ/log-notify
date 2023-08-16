<?php

return [

    //通知接口
    'api'    => array(
        'default'   => [
            'type'              => 'dingtalk',  //通知接口类型（钉钉）
            'access_token'      => '983d92ec8a828f538cb18a94b927babe9fdbbad116871b5ebe742547e241c675',
            'secret'            => 'SECc2e0fffe0207e5e3d0e823be29f289f146f91fd83e11951cf79e530b5f2666a0',
        ],
    ),

    //缓存
    'redis'     => [
        'host'  => '127.0.0.1',
        'port'  => 6379,
    ],

    //报警模块
    'modules'    => array(
        'order'   => [
            'api'               => 'default',   //通知接口
            'label'             => '订单中心',    //客户化名称
            'interval'          => 3600,        //统计周期
            'times'             => 3,           //warning触发报警次数
            'frequency'         => 600,         //warning报警间隔
            'error.frequency'   => 1,          //error报警间隔
        ],
    ),
];
