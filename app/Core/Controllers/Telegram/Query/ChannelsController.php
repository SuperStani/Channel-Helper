<?php


namespace App\Core\Controllers\Telegram\Query;


use App\Core\Controllers\Telegram\QueryController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Repositories\ChannelsRepository;
use App\Integrations\Telegram\Query;

class ChannelsController extends QueryController
{
    private ChannelsRepository $channelsRepository;

    public function __construct(
        Query $query,
        UserController $user,
        LoggerInterface $logger,
        ChannelsRepository $channelsRepository
    )
    {
        parent::__construct($query, $user, $logger);
        $this->channelsRepository = $channelsRepository;
    }

    public function home(): ?array
    {
        $this->user->page("");
        $channels = $this->channelsRepository->getAll();
        foreach ($channels as $channel) {
            $menu[] = [
                ["text" => $channel->getTitle(), "callback_data" => "Channels:null"],
                ["text" => get_button("delete"), "callback_data" => "Channels:delete|" . $channel->getId()]
            ];
        }
        $menu[] = [["text" => get_button('add_channel'), "callback_data" => "Channels:add"]];
        $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
        return $this->query->message->edit(get_string('channels_home'), $menu);
    }

    public function delete(int $id): ?array
    {
        $this->channelsRepository->delete($id);
        return $this->home();
    }

    public function add(): ?array
    {
        $menu[] = [["text" => get_button('back'), "callback_data" => "Channels:home"]];
        $message = $this->query->message->edit(get_string("new_channel"), $menu)['result'];
        $this->user->page("Channels:add|" . $message['message_id']);
        return $message;
    }
}