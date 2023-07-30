<?php


namespace App\Configs;


interface GeneralConfigurations
{
    public const LOGGER_PATH = "/var/log/ChannelsHelper/";

    public const BOT_TOKEN = "";

    public const ADMINS = [
        170172016,
        406343901,
        5567828007
    ];

    public const API_ENDPOINT = "https://channels-helper.xohosting.it/bot/api.php";

    public const MARKDOWN_INVISIBLE = "ㅤ";

    public const HTML_INVISIBLE = "&#8203;";
}