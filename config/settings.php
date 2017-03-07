<?php
/**
 * settings.php
 *
 * @author: lanse
 * @time: 17-01-06 下午8:03
 */

return [
    'displayErrorDetails' => true,
    'cookie_domain' => '.guazi.com',
    'sso' => [
        'login' => 'http://staff.guazi.com/Account/LogIn?returnUrl=',
        'login_out' => 'http://staff.guazi.com/Account/LogOut?returnUrl=',
        'identity' => 'http://staff.guazi.com/Account/Identity'
    ],
];