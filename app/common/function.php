<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/7
 * Time: 上午9:37
 */

if (!function_exists('env')) {
    function env($key) {
        if (function_exists('putenv')) {
            return getenv($key);
        } else {
            return $_ENV[$key];
        }
    }
}

function now_datetime() {
    return date('Y-m-d H:i:s');
}

function now_datetime_micro() {
    $t = microtime(true);
    $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
    $d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));
    $datetime_micro = $d->format("Y-m-d H:i:s.u");
    return substr($datetime_micro, 0, strlen($datetime_micro) - 3);
}

function output_success($text) {
//    echo "\033[33;32msuccess\033[0m||" . now_datetime_micro() . '||' . $text . PHP_EOL;
    echo "[success]||" . now_datetime_micro() . '||' . $text . PHP_EOL;
}

function output_error($text) {
//    echo "\033[33;31m error \033[0m||" . now_datetime_micro() . '||' . $text . PHP_EOL;
    echo "[ error ]||" . now_datetime_micro() . '||' . $text . PHP_EOL;
}

function output_warning($text) {
//    echo "\033[33;30mwarning\033[0m||" . now_datetime_micro() . '||' . $text . PHP_EOL;
    echo "[warning]||" . now_datetime_micro() . '||' . $text . PHP_EOL;
}

function output_info($text) {
//    echo "\033[33;33m info  \033[0m||" . now_datetime_micro() . '||' . $text . PHP_EOL;
    echo "[ info  ]||" . now_datetime_micro() . '||' . $text . PHP_EOL;
}

// 获取 列表页 url
function get_task_list_url($first_category, $page) {
    $mapping = [
        'wzkf' => 'https://task.zbj.com/t-wzkf/page{page}.html',
    ];
    return str_replace(['{page}'], [$page], $mapping[$first_category]);
}

function get_first_category_name($first_category_en) {
    $mapping = [
        'wzkf' => '网站建设',
    ];
    return $mapping[$first_category_en];
}

function array_get(array $array, $key) {
    if (isset($array[$key])) {
        return $array[$key];
    }
    return null;
}
