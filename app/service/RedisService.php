<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/11
 * Time: 下午9:10
 */
namespace App\Service;

use Predis\Client;

class RedisService 
{
    public static function getInstance($dsn = '')
    {
        if ($dsn === '') {
            $dsn = 'tcp://127.0.0.1:6379';
        }
        return new Client($dsn);
    }

    public static function getKey($key, $params = [])
    {
        $project = 'selenium_php:';
        $mapping = [
            'already_fetch_taskid' => $project . 'already_fetch_taskid:'  . array_get($params, 0) . ':taskid:' . array_get($params, 1),
            'wait_spider_queue' => $project . 'wait_spider_queue',
            'wait_spider_set' => $project . 'wait_spider_set',
        ];
        return $mapping[$key];
    }
}
