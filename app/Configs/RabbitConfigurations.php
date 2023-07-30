<?php


namespace App\Configs;


interface RabbitConfigurations
{
    public const HOST = "localhost";

    public const PORT = 5672;

    public const USER = "guest";

    public const PASSWORD = "guest";

    public const VHOST = "/";
}