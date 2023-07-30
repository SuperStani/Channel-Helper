<?php

require_once __DIR__ . "/../../vendor/autoload.php";
$container = require __DIR__ . "/../Configs/DIConfigs.php";

$app = $container->get(App\Core\RabbitLogic\Consumers\PostsConsumer::class);

$command = $argv[1] ?? '';

switch($command) {
    case 'start':
        $app->init('PostSenderWorker');
        $app->start();
    case 'stop':
        $app->init('PostSenderWorker');
        $app->stop();
    default:
        die('Unknownm command "' . $command . '"');
}