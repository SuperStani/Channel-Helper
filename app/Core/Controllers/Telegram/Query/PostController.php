<?php

namespace App\Core\Controllers\Telegram\Query;


use App\Core\Controllers\Telegram\QueryController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Entities\Schedule;
use App\Core\ORM\Repositories\ChannelsRepository;
use App\Core\ORM\Repositories\PostsRepository;
use App\Core\ORM\Repositories\SchedulesRepository;
use App\Core\Services\CacheService;
use App\Core\Services\PostQueueService;
use App\Integrations\Telegram\Query;

class PostController extends QueryController
{
    private CacheService $cacheService;
    private ChannelsRepository $channelsRepository;
    private PostsRepository $postRepository;
    private SchedulesRepository $schedulesRepository;
    private PostQueueService $postQueueService;

    public function __construct(
        Query $query,
        UserController $user,
        LoggerInterface $logger,
        CacheService $cacheService,
        ChannelsRepository $channelsRepository,
        PostsRepository $postRepository,
        SchedulesRepository $schedulesRepository,
        PostQueueService $postQueueService
    )
    {
        parent::__construct($query, $user, $logger);
        $this->cacheService = $cacheService;
        $this->channelsRepository = $channelsRepository;
        $this->postRepository = $postRepository;
        $this->schedulesRepository = $schedulesRepository;
        $this->postQueueService = $postQueueService;
    }

    public function new(?int $postId = null): ?array
    {
        $this->user->page("");
        if ($postId == null) {
            $postId = $this->cacheService->createTempPost($this->user->id);
        }
        $post = $this->cacheService->getTempPost($postId);
        $menu[] = [["text" => get_button("next"), "callback_data" => "Post:mediaOrCaption|$postId"]];
        $menu[] = [
            ["text" => get_button('notify'), "callback_data" => "Post:null"],
            ["text" => $post->isNotifyEnabled() ? "✅" : "❌", "callback_data" => "Post:enableNotify|$postId|" . ($post->isNotifyEnabled() ? 0 : 1)]
        ];

        $menu[] = [
            ["text" => get_button('formatter'), "callback_data" => "Post:null"],
            ["text" => $post->getTextFormat(), "callback_data" => "Post:textFormat|$postId|" . ($post->getTextFormat() == 'Markdown' ? 1 : 0)]
        ];

        $menu[] = [
            ["text" => get_button('preview_link'), "callback_data" => "Post:null"],
            ["text" => $post->isPreviewLinkAllowed() ? "✅" : "❌", "callback_data" => "Post:previewLinkAllowed|$postId|" . ($post->isPreviewLinkAllowed() ? 0 : 1)]
        ];
        $menu[] = [
            ["text" => get_button('protected'), "callback_data" => "Post:null"],
            ["text" => $post->isProtected() ? "✅" : "❌", "callback_data" => "Post:isProtected|$postId|" . ($post->isProtected() ? 0 : 1)]
        ];
        $menu[] = [["text" => get_button("back"), "callback_data" => "Home:start"]];
        if ($postId == null) {
            $this->query->message->delete();
            return $this->query->message->reply(get_string("new_post"), $menu);
        }
        return $this->query->message->edit(get_string("new_post"), $menu);
    }

    public function mediaOrCaption(int $postId)
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $this->user->page("");
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:new|$postId"]
        ];
        $this->user->page("Post:mediaOrCaption|$postId");
        $message = $this->query->message->edit(get_string('init_new_post'), $menu)['result'];

        $post->setPrivateTempMessageId($message['message_id']);
        $post->setCaption(null);
        $post->setMediaId(null);
        $post->setMediaType(null);
        $this->cacheService->updateTempPost($postId, $post);
        return $message;
    }

    public function noCaption(int $postId): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $this->user->page("");
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $post->setLinkMediaToCaption(false);
        $post->setKeyboard([]);
        $this->cacheService->updateTempPost($postId, $post);
        $menu[] = [["text" => get_button('no_keyboard'), "callback_data" => "Post:noKeyboard|$postId"]];
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
        ];
        $this->user->page("Post:sendKeyboard|$postId");
        return $this->query->message->edit(get_string('keyboard_new_post'), $menu);
    }

    public function sendKeyboard(int $postId): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $this->user->page("");
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $post->setKeyboard(null);
        $post->setDatetimeScheduleSend(null);
        $post->setDatetimeScheduleEnd(null);
        $this->cacheService->updateTempPost($postId, $post);
        $menu[] = [["text" => get_button('no_keyboard'), "callback_data" => "Post:noKeyboard|$postId"]];
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
        ];
        $this->user->page("Post:sendKeyboard|$postId");
        return $this->query->message->edit(get_string('keyboard_new_post'), $menu);
    }


    public function synopsisNewPost(int $postId): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        $menu[] = [["text" => ($post->getDatetimeScheduleSend() != null || $post->getDatetimeScheduleEnd()) ? get_button('schedule_post') : get_button('send_post'), "callback_data" => "Post:selectChannelsToSend|$postId"]];
        $menu[] = [["text" => get_button('schedule_send'), "callback_data" => "Post:scheduleSend|$postId"]];
        $menu[] = [["text" => get_button('schedule_end'), "callback_data" => "Post:scheduleEnd|$postId"]];
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:sendKeyboard|$postId"]
        ];
        $text = get_string('new_post_synopsis') . "\n";
        if ($post->getDatetimeScheduleSend() !== null) {
            $text .= "\nData inizio: *" . $post->getDatetimeScheduleSend() . "*";
        }
        if ($post->getDatetimeScheduleEnd() !== null) {
            $text .= "\nData eliminazione: *" . $post->getDatetimeScheduleEnd() . "*";
        }
        $this->user->page('');
        return $this->query->message->edit($text, $menu);
    }

    public function selectChannelsToSend(int $postId): ?array
    {
        $channels = $this->channelsRepository->getAll();
        $selectedChannels = $this->cacheService->getChannelsToSend($postId);
        foreach($channels as $channel) {
            $menu[] = [["text" => $channel->getTitle() . (in_array($channel->getId(), $selectedChannels) ? " ✅" : ""), "callback_data" => "Post:selectChannel|$postId|" . $channel->getId()]];
        }
        $menu[] = [["text" => get_button('finish_post'), "callback_data" => "Post:finish|$postId"]];
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:synopsisNewPost|$postId"]
        ];
        return $this->query->message->edit(get_string("select_channel_to_send"), $menu);
    }

    public function selectChannel(int $postId, int $channelId): ?array
    {
        $selectedChannels = $this->cacheService->getChannelsToSend($postId);
        if(in_array($channelId, $selectedChannels)) {
            $selectedChannels = array_diff($selectedChannels, [$channelId]);
            $this->logger->warning(json_encode($selectedChannels));
        } else {
            $selectedChannels[] = $channelId;
        }
        $this->cacheService->setChannelsToSend($postId, $selectedChannels);
        return $this->selectChannelsToSend($postId);
    }

    public function finish(int $postId) {
        $post = $this->cacheService->getTempPost($postId);
        $this->postRepository->save($post);
        $selectedChannels = $this->cacheService->getChannelsToSend($postId);
        if(count($selectedChannels) == 0) {
            return $this->query->alert(get_string('not_selected_channels'));
        }
        if($post->getDatetimeScheduleSend() !== null || $post->getDatetimeScheduleEnd() !== null) {
            foreach($selectedChannels as $channel) {
                $schedule = new Schedule();
                $schedule->setPostId($post->getId());
                $schedule->setChannelId($channel);
                $schedule->setDatetimeSend($post->getDatetimeScheduleSend());
                $schedule->setDatetimeEnd($post->getDatetimeScheduleEnd());
                $schedule_id = $this->schedulesRepository->save($schedule);
                if($post->getDatetimeScheduleSend() === null) {
                    $this->postQueueService->reqSendPost($post->getId(), $channel, $schedule_id);
                }
            }
            $text = get_string('post_scheduled');
        } else {
            foreach($selectedChannels as $channel) {
                $this->postQueueService->reqSendPost($post->getId(), $channel);
            }
            $text = get_string('post_sent');
        }
        $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
        $this->query->message->edit($text, $menu);
    }

    public function scheduleSend(int $postId)
    {
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:synopsisNewPost|$postId"]
        ];
        $this->user->page("Post:schedule|TO_SEND|$postId");
        $this->query->message->edit(get_string('schedule_send'), $menu);
    }

    public function scheduleEnd(int $postId)
    {
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:synopsisNewPost|$postId"]
        ];
        $this->user->page("Post:schedule|TO_END|$postId");
        $this->query->message->edit(get_string('schedule_end'), $menu);
    }

    public function sendCaption(int $postId): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        $menu[] = [["text" => get_button('no_caption'), "callback_data" => "Post:noCaption|$postId"]];
        $menu[] = [
            ["text" => get_button('link_media'), "callback_data" => "Post:null"],
            ["text" => $post->isLinkMediaToCaption() ? "✅" : "❌", "callback_data" => "Post:linkMedia|$postId|" . ($post->isLinkMediaToCaption() ? 0 : 1)]
        ];
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
        ];
        $this->user->page("Post:sendCaption|$postId");
        $text = get_string('caption_new_post', 'it', $post->getTextFormat());
        return $this->query->message->edit($text, $menu);
    }


    public function noKeyboard(int $postId): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        $post->setKeyboard(null);
        $this->cacheService->updateTempPost($postId, $post);
        return $this->synopsisNewPost($postId);
    }

    public function enableNotify($postId, $value): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $this->user->page("");
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $post->setNotifyEnabled((bool)$value);
        $this->cacheService->updateTempPost($postId, $post);
        return $this->new($postId);
    }

    public function linkMedia(int $postId, $value): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        $post->setLinkMediaToCaption((bool)$value);
        $this->cacheService->updateTempPost($postId, $post);
        return $this->sendCaption($postId);
    }

    public function textFormat($postId, $value): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $formats = ["MarkdownV2", "HTML"];
        $post->setTextFormat($formats[$value]);
        $this->cacheService->updateTempPost($postId, $post);
        return $this->new($postId);
    }

    public function previewLinkAllowed($postId, $value): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $post->setPreviewLinkAllowed((bool)$value);
        $this->cacheService->updateTempPost($postId, $post);
        return $this->new($postId);
    }

    public function isProtected($postId, $value): ?array
    {
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->query->message->edit("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        $post->setIsProtected((bool)$value);
        $this->cacheService->updateTempPost($postId, $post);
        return $this->new($postId);
    }

}
