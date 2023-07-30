<?php


namespace App\Core\Controllers;


use App\Configs\GeneralConfigurations;
use App\Integrations\Telegram\TelegramClient;

class APIController
{
    private ?string $action;

    private array $response;


    public function __construct()
    {
        $this->response = [
            "result" => false,
            "message" => "Bad request"
        ];

    }

    public function init()
    {
        //header("Content-Type: application/json");
        $this->action = $_GET['action'] ?? null;
    }

    public function process()
    {
        switch ($this->action) {
            case 'getPhoto':
                $this->displayPhoto();
                break;
        }
    }

    public function displayPhoto()
    {
        if ($_GET['id']) {
            $url = "https://api.telegram.org/file/bot" . GeneralConfigurations::BOT_TOKEN . "/" . TelegramClient::getFile($_GET['id'])['result']['file_path'];
            $imageSize = getimagesize($url);
            header("Content-type: " . $imageSize['mime']);
            echo file_get_contents($url);
        }
    }
}