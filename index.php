<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.06.2019
 * Time: 18:00
 */

define('APP_PATH', __DIR__ . '/');

define('APP_DEBUG', true);

require(APP_PATH . 'loader/loader.php');

$config = require(APP_PATH . 'config/config.php');

(new loader\loader($config))->run();



