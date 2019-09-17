<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/7
 * Time: 上午10:11
 */

require_once(dirname(__FILE__) . "/../../index.php");

// php scripts/zhubajie/spider_detail.php >> ./spider_detail.log 2>&1
$zhubajie = new \App\Model\SpiderZhuBaJie();
$zhubajie->setDriver();
while (true) {
    $status = $zhubajie->taskDetail();
    if ($status === false) {
        break;
    }

    $sleep_sec = rand(5, 20);
    output_info('sleep: ' . $sleep_sec . ' second');
    sleep($sleep_sec);
}
