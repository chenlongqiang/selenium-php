<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/7
 * Time: 上午10:11
 */

require_once(dirname(__FILE__) . "/../../index.php");

/*
it综合服务|https://task.zbj.com/t-jsfwzbj/pageNUM.html
网站建设|https://task.zbj.com/t-wzkf/pageNUM.html
软件开发|https://task.zbj.com/t-rjkf/pageNUM.html
微信开发|https://task.zbj.com/t-wxptkf/pageNUM.html
*/

// php scripts/zhubajie/spider_list.php >> ./spider_list.log 2>&1
$first_category = 'wzkf';
$zhubajie = new \App\Model\SpiderZhuBaJie();
$zhubajie->setDriver();
foreach (range(2, 5) as $page) {
    $zhubajie->taskList($first_category, $page);

    $sleep_sec = rand(10, 30);
    output_info('sleep: ' . $sleep_sec . ' second');
    sleep($sleep_sec);
}
