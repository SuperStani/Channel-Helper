<?php


namespace App\Core\Controllers\Telegram\Messages;


use App\Core\Controllers\Telegram\MessageController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Entities\Channel;
use App\Core\ORM\Repositories\ChannelsRepository;
use App\Integrations\Telegram\Message;
use App\Integrations\Telegram\TelegramClient;

class ChannelsController extends MessageController
{
    private ChannelsRepository $channelsRepository;

    public function __construct(
        Message $message,
        UserController $user,
        LoggerInterface $logger,
        ChannelsRepository $channelsRepository
    )
    {
        parent::__construct($message, $user, $logger);
        $this->channelsRepository = $channelsRepository;
    }

    public function add($message_id): ?array
    {
        $this->message->delete();
        if (isset($this->message->forward_from_chat)) {
            $channel = new Channel();
            $channel->setId($this->message->forward_from_chat->id);
            $channel->setTitle($this->message->forward_from_chat->title);
            $channel->setAddedBy($this->user->id);
            $this->channelsRepository->save($channel);
            $menu[] = [["text" => get_button('back'), "callback_data" => "Channels:home"]];
            return TelegramClient::editMessageText(
                $this->message->chat_id,
                $message_id,
                null,
                get_string('channel_added'),
                'Markdown',
                null,
                null,
                ['inline_keyboard' => $menu]
            );
        }
        return null;
    }
}