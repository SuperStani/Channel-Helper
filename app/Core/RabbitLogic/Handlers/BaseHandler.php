<?php


namespace App\Core\RabbitLogic\Handlers;


abstract class BaseHandler
{
    abstract function handle(string $message);
}