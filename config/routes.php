<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-21
 * Time: 下午2:26
 * Brief： 路由文件
 */
use NoahBuscher\Macaw\Macaw;

Macaw::get('fuck', function() {
    echo "成功！";
});

Macaw::get('(:all)', function($fu) {
    echo "未匹配到路由<br>".$fu;
});

Macaw::dispatch();