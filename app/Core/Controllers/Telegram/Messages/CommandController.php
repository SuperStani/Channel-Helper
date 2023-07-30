<?php

namespace App\Core\Controllers\Telegram\Messages;

use App\Core\Controllers\Telegram\MessageController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Integrations\Telegram\Message;


class CommandController extends MessageController
{

    public function __construct(
        Message $message,
        UserController $user,
        LoggerInterface $logger
    )
    {
        parent::__construct($message, $user, $logger);
    }

    public function start($param = null): ?array
    {
        if ($this->user->isAdmin()) {
            $this->user->save();
            if (!$param) {
                $this->user->page();
                $menu[] = [
                    ["text" => get_button('new_post'), "callback_data" => "Post:new"],
                    ["text" => get_button('history_post'), "callback_data" => "Schedules:home"]
                ];
                $menu[] = [["text" => get_button("channels_management"), "callback_data" => "Channels:home"]];
                $text = "Test message";
                return $this->message->reply($text, $menu);
            } else {
                $param = explode("_", $param);
                switch ($param[0]) {
                    default:
                        break;
                }
            }
        }
        return [];
    }


    public function check()
    {
        $e = explode(" ", $this->message->text);
        $method = str_replace("/", "", $e[0]);
        unset($e[0]);
        return $this->callAction($method, $e);
    }
}
