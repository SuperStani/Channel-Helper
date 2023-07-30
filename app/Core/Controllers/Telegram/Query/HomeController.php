<?php


namespace App\Core\Controllers\Telegram\Query;


use App\Core\Controllers\Telegram\QueryController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Integrations\Telegram\Query;

class HomeController extends QueryController
{

    public function __construct(
        Query $query,
        UserController $user,
        LoggerInterface $logger
    )
    {
        parent::__construct($query, $user, $logger);
    }

    public function start()
    {
        $this->query->message->delete();
        $this->user->page();
        $menu[] = [
            ["text" => get_button('new_post'), "callback_data" => "Post:new"],
            ["text" => get_button('history_post'), "callback_data" => "Schedules:home"]
        ];
        $menu[] = [["text" => get_button("channels_management"), "callback_data" => "Channels:home"]];
        $text = "Test message";
        return $this->query->message->reply($text, $menu);
    }
}