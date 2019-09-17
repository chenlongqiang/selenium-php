<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/11
 * Time: 下午9:10
 */
namespace App\Service;

use ParagonIE\EasyDB\Factory;

class EasyDbService 
{
    public static function getInstance($dsn = [])
    {
        return Factory::create(
            'mysql:host=' . env('MYSQL_HOST') . ';dbname=' . env('MYSQL_DBNAME') . ';charset=utf8',
            env('MYSQL_USERNAME'),
            env('MYSQL_PASSWORD')
        );
    }
}
