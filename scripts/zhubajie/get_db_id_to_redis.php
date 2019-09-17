<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/11
 * Time: 下午9:38
 */

require_once(dirname(__FILE__) . "/../../index.php");

$zhubajie = new \App\Model\SpiderZhuBaJie();
$zhubajie->getDbIdToRedis();

