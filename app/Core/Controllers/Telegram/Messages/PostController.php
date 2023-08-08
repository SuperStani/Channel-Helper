<?php


namespace App\Core\Controllers\Telegram\Messages;


use App\Core\Controllers\Telegram\MessageController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Core\Modules\DatetimeUtility;
use App\Core\Services\CacheService;
use App\Core\Services\ParserService;
use App\Integrations\Telegram\Message;
use App\Integrations\Telegram\TelegramClient;
use App\Integrations\Telegram\Utility;

class PostController extends MessageController
{
    private CacheService $cacheService;

    public function __construct(
        Message $message,
        UserController $user,
        LoggerInterface $logger,
        CacheService $cacheService,
    )
    {
        parent::__construct($message, $user, $logger);
        $this->cacheService = $cacheService;
    }

    public function mediaOrCaption(int $postId): ?array
    {
        $this->message->delete();
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->message->reply("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        if (isset($this->message->text)) {
            if ($this->message->entities === null) {
                if ($post->getTextFormat() == 'Markdown') {
                    $text = ParserService::validateMarkdown($this->message->text);
                } else {
                    $text = ParserService::validateHTML($this->message->text);
                }
                if ($text === false) {
                    $menu[] = [
                        ["text" => get_button('menu'), "callback_data" => "Home:start"],
                        ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
                    ];
                    return TelegramClient::editMessageText(
                        chat_id: $this->message->chat_id,
                        message_id: $post->getPrivateTempMessageId(),
                        text: get_string('format_error'),
                        parse_mode: 'Markdown',
                        reply_markup: ['inline_keyboard' => $menu]
                    );
                }
            } else {
                $text = ParserService::entitiesToHtml($this->message->text, $this->message->entities);
                $post->setTextFormat('HTML');
            }
            $post->setCaption($text);
            $this->cacheService->updateTempPost($postId, $post);
            $menu[] = [["text" => get_button('no_keyboard'), "callback_data" => "Post:noKeyboard|$postId"]];
            $menu[] = [
                ["text" => get_button('menu'), "callback_data" => "Home:start"],
                ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
            ];
            $text = get_string('keyboard_new_post');
            $this->user->page("Post:sendKeyboard|$postId");
        } else {
            if (isset($this->message->photo)) {
                $media = end($this->message->photo);
                $media_id = $media->file_id;
                $media_type = 'photo';
            } elseif (isset($this->message->video)) {
                $media = end($this->message->video);
                $media_id = $media->file_id;
                $media_type = 'video';
            } elseif (isset($this->message->animation)) {
                $media = $this->message->animation;
                $media_id = $media->file_id;
                $media_type = 'gif';
            } elseif (isset($this->message->document)) {
                $media = $this->message->document;
                $media_id = $media->file_id;
                $media_type = 'document';
            } elseif (isset($this->message->audio)) {
                $media = end($this->message->audio);
                $media_id = $media->file_id;
                $media_type = 'audio';
            } else {
                $media_id = null;
                $media_type = null;
            }
            if ($media_id !== null) {
                $post->setMediaType($media_type);
                $post->setMediaId($media_id);
                $this->cacheService->updateTempPost($postId, $post);
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
            } else {
                $menu[] = [
                    ["text" => get_button('menu'), "callback_data" => "Home:start"],
                    ["text" => get_button('back'), "callback_data" => "Post:new|$postId"]
                ];
                $text = get_string('error_media');
            }
        }
        return TelegramClient::editMessageText(
            $this->message->chat_id,
            $post->getPrivateTempMessageId(),
            null,
            $text,
            'Markdown',
            null,
            null,
            ['inline_keyboard' => $menu]
        );
    }

    public function sendCaption(int $postId): ?array
    {
        $this->message->delete();
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->message->reply("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        if ($this->message->entities === null) {
            if ($post->getTextFormat() == 'Markdown') {
                $text = ParserService::validateMarkdown($this->message->text);
            } else {
                $text = ParserService::validateHTML($this->message->text);
            }
            if ($text === false) {
                $menu[] = [
                    ["text" => get_button('menu'), "callback_data" => "Home:start"],
                    ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
                ];
                return TelegramClient::editMessageText(
                    chat_id: $this->message->chat_id,
                    message_id: $post->getPrivateTempMessageId(),
                    text: get_string('format_error'),
                    parse_mode: 'Markdown',
                    reply_markup: ['inline_keyboard' => $menu]
                );
            }
        } else {
            $text = ParserService::entitiesToHtml($this->message->text, $this->message->entities);
            $post->setTextFormat('HTML');
        }
        $post->setCaption($text);
        $post->setKeyboard(null);
        $this->cacheService->updateTempPost($postId, $post);
        $menu[] = [["text" => get_button('no_keyboard'), "callback_data" => "Post:noKeyboard|$postId"]];
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:mediaOrCaption|$postId"]
        ];
        $text = get_string('keyboard_new_post');
        $this->user->page("Post:sendKeyboard|$postId");
        return TelegramClient::editMessageText(
            $this->message->chat_id,
            $post->getPrivateTempMessageId(),
            null,
            $text,
            'Markdown',
            null,
            null,
            ['inline_keyboard' => $menu]
        );
    }

    public function sendKeyboard(int $postId): ?array
    {
        $this->message->delete();
        $post = $this->cacheService->getTempPost($postId);
        if ($post == null) {
            $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
            return $this->message->reply("La sessione del post è scaduta!\nPuoi ricrearlo quando vuoi!", $menu);
        }
        if (isset($this->message->text)) {
            $buttons = Utility::buildKeyboardFromRawText($this->message->text);
            if ($this->message->reply(get_string('keyboard_preview'), $buttons)['ok']) {
                $post = $this->cacheService->getTempPost($postId);
                $post->setKeyboard($buttons);
                $menu[] = [["text" => get_button('send_post'), "callback_data" => "Post:selectChannelsToSend|$postId"]];
                $menu[] = [["text" => get_button('schedule_send'), "callback_data" => "Post:scheduleSend|$postId"]];
                $menu[] = [["text" => get_button('schedule_end'), "callback_data" => "Post:scheduleEnd|$postId"]];
                $menu[] = [
                    ["text" => get_button('menu'), "callback_data" => "Home:start"],
                    ["text" => get_button('back'), "callback_data" => "Post:sendKeyboard|$postId"]
                ];
                $this->user->page('');
                $message = $this->message->reply(get_string('new_post_synopsis'), $menu)['result'];
                TelegramClient::deleteMessage($this->message->chat_id, $post->getPrivateTempMessageId());
                $post->setPrivateTempMessageId($message['message_id']);
                $this->cacheService->updateTempPost($postId, $post);
            }
        }
    }

    public function schedule(string $type, int $postId): ?array
    {
        $this->message->delete();
        $post = $this->cacheService->getTempPost($postId);
        if (stripos($this->message->text, "oggi alle") === 0) {
            $hour = explode("alle ", $this->message->text)[1] ?? '';
            $this->logger->warning($hour);
            $datetime = DatetimeUtility::addIntervalToNow(0, $hour);
        } elseif (stripos($this->message->text, "domani alle") === 0) {
            $hour = explode("alle ", $this->message->text)[1] ?? '';
            $this->logger->warning($hour);
            $datetime = DatetimeUtility::addIntervalToNow(1, $hour);
        } elseif (stripos($this->message->text, "dopodomani alle") === 0) {
            $hour = explode("alle ", $this->message->text)[1] ?? '';
            $this->logger->warning($hour);
            $datetime = DatetimeUtility::addIntervalToNow(2, $hour);
        } elseif (stripos($this->message->text, "tra") === 0) {
            $e = explode("alle ", str_replace("tra ", "", $this->message->text));
            $days = trim(str_replace("giorni", "", $e[0]));
            $hour = $e[1] ?? '';
            $this->logger->warning($hour, $days);
            $datetime = DatetimeUtility::addIntervalToNow($days, $hour);
        } else {
            $datetime = DatetimeUtility::getMySQLDateTime($this->message->text);
        }
        if ($datetime !== false) {
            if ($type == 'TO_SEND') {
                $post->setDatetimeScheduleSend($datetime);
            } else {
                $post->setDatetimeScheduleEnd($datetime);
            }
            $this->cacheService->updateTempPost($postId, $post);
            $text = get_string('schedule_ok', 'it', $datetime);
        } else {
            $text = get_string("schedule_nok");
        }
        $menu[] = [
            ["text" => get_button('menu'), "callback_data" => "Home:start"],
            ["text" => get_button('back'), "callback_data" => "Post:synopsisNewPost|$postId"]
        ];
        return TelegramClient::editMessageText(
            $this->message->chat_id,
            $post->getPrivateTempMessageId(),
            null,
            $text,
            'Markdown',
            null,
            null,
            ['inline_keyboard' => $menu]
        );
    }

}