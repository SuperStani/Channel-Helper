<?php

require_once __DIR__ . "/../../vendor/autoload.php";
$container = require __DIR__ . "/../Configs/DIConfigs.php";

$app = $container->get(App\Core\Services\PostsInitializerService::class);

$app->initializePostsToSend();
$app->initializePostsToDelete();