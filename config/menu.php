<?php
/**
 * menu.php
 *
 * @author: lanse
 * @time  : 17-01-06 下午7:17
 */

return [
    [
        'name'  => '保卖工作台',
        'style' => 'fa-laptop',
        'items' => [
            [
                'name'      => '保卖处理',
                'uri'       => 'admin/consign/list',
                'urlParams' => [],
            ],
            [
                'name'      => '收购处理',
                'uri'       => 'admin/consign/acquisition',
                'urlParams' => [],
            ],
        ],
    ],
    [
        'name'  => '保卖工单管理',
        'style' => 'fa-users',
        'items' => [
            [
                'name'      => '分单列表',
                'uri'       => 'admin/task/assign',
                'urlParams' => [],
            ],
            [
                'name'      => '任务列表',
                'uri'       => 'admin/task/list',
                'urlParams' => [],
            ],
        ],
    ],
    [
        'name'  => '后台配置',
        'style' => 'fa-cog',
        'items' => [
            [
                'name'      => '保卖人员地点配置',
                'uri'       => 'admin/address/user',
                'urlParams' => [],
            ],
            [
                'name'      => '车辆停放地点配置',
                'uri'       => 'admin/address/car',
                'urlParams' => [],
            ],
        ],
    ],
];
