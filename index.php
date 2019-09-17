<?php
/**
 * Created by PhpStorm.
 * User: luka-chen
 * Date: 19/9/7
 * Time: 上午9:36
 */

define('ROOT', dirname(__FILE__));

require_once(ROOT . '/vendor/autoload.php');

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

Predis\Autoloader::register();