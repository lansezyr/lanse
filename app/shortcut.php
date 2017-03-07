<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-3-7
 * Time: 下午1:57
 */
use ROOT\App\Start;

function service($service)
{
    return Start::getSlim()->getContainer()->get($service);
}

function getConfig($filename)
{
    return Start::getConfig($filename);
}