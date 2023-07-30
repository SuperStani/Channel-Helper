<?php
require_once __DIR__ . "/../../../vendor/autoload.php";
$container = require __DIR__ . "/../../Configs/DIConfigs.php";

$app = $container->get(App\Core\Controllers\APIController::class);

$app->init();
$app->process();