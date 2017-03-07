<?php
/**
 * service.php
 *
 * @author: lanse
 * @time: 17-01-06 下午6:18
 */

return [
    'menu'  => function () {
        return require __DIR__ . '/menu.php';
    },
    'routes'  => function () {
        return require __DIR__ . '/routes.php';
    },
    'settings'  => require __DIR__ . '/settings.php',
    'template'  => function () {
        $template = new \Root\Library\Util\ViewTemplateUtil();
        return $template;
    },
    'database'  => function () {
        return require __DIR__ . '/db_config.php';
    },

];