<?php
/**
 * menu.php
 * @author: lanse
 * @time  : 17-01-06 下午7:17
 */

return [
    [
        'name'  => '人才管理',
        'style' => 'fa-users',
        'items' => [
            [
                'name'      => '候选人列表',
                'uri'       => 'candidate/list',
                'urlParams' => [],
            ],
            [
                'name'      => '候选人录入',
                'uri'       => 'candidate/add',
                'urlParams' => [],
            ]
        ],
    ],
];
