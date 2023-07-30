<?php


namespace App\Core\Services;


use App\Configs\GeneralConfigurations;
use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Entities\PostMessage;
use App\Core\ORM\Repositories\MessagesRepository;
use App\Core\ORM\Repositories\PostsRepository;
use App\Core\ORM\Repositories\SchedulesRepository;
use App\Integrations\Telegram\TelegramClient;

class PostsManagerService
{
    private LoggerInterface $logger;
    private PostsRepository $postsRepository;
    private SchedulesRepository $schedulesRepository;
    private MessagesRepository $messagesRepository;

    public function __construct(
        LoggerInterface $logger,
        PostsRepository $postRepository,
        SchedulesRepository $schedulesRepository,
        MessagesRepository $messagesRepository
    )
    {
        $this->logger = $logger;
        $this->postsRepository = $postRepository;
        $this->schedulesRepository = $schedulesRepository;
        $this->messagesRepository = $messagesRepository;
    }

    public function sendPost(int $postId, int $channel_id, int $schedule_id = null)
    {
        $post = $this->postsRepository->getById($postId);
        if ($post->getMediaId() == null || ($post->isLinkMediaToCaption() && $post->getMediaType() == 'photo')) {
            $text = $post->getCaption();
            if ($post->isLinkMediaToCaption()) {
                $mediaUrl = GeneralConfigurations::API_ENDPOINT . "?action=getPhoto&id=" . $post->getMediaId();
                if ($post->getTextFormat() == 'Markdown') {
                    $text .= " [" . GeneralConfigurations::MARKDOWN_INVISIBLE . "]($mediaUrl)";
                } else {
                    $text .= " <a href='$mediaUrl'>" . GeneralConfigurations::HTML_INVISIBLE . "</a>";
                }
            }
            $messageTg = TelegramClient::sendMessage(
                $channel_id,
                $text,
                $post->getTextFormat(),
                null,
                !$post->isPreviewLinkAllowed(),
                !$post->isNotifyEnabled(),
                $post->isProtected(),
                null,
                null,
                $post->getKeyboard() !== null ? ['inline_keyboard' => $post->getKeyboard()] : null
            );
        } else {
            if ($post->getMediaType() == 'photo') {
                $messageTg = TelegramClient::sendPhoto(
                    $channel_id,
                    $post->getMediaId(),
                    $post->getCaption(),
                    $post->getKeyboard() ?? null,
                    $post->getTextFormat(),
                    null,
                    !$post->isNotifyEnabled(),
                    $post->isProtected(),
                    null,
                    null
                );
            } elseif ($post->getMediaType() == 'video') {
                $messageTg = TelegramClient::sendVideo(
                    $channel_id,
                    $post->getMediaId(),
                    null,
                    null,
                    null,
                    null,
                    $post->getCaption(),
                    $post->getTextFormat(),
                    null,
                    true,
                    !$post->isNotifyEnabled(),
                    $post->isProtected(),
                    null,
                    null,
                    $post->getKeyboard() !== null ? ['inline_keyboard' => $post->getKeyboard()] : null,
                );
            } elseif ($post->getMediaType() == 'gif') {
                $messageTg = TelegramClient::sendAnimation(
                    $channel_id,
                    $post->getMediaId(),
                    null,
                    null,
                    null,
                    null,
                    $post->getCaption(),
                    $post->getTextFormat(),
                    null,
                    !$post->isNotifyEnabled(),
                    $post->isProtected(),
                    null,
                    null,
                    $post->getKeyboard() !== null ? ['inline_keyboard' => $post->getKeyboard()] : null,
                );
            }
        }

        if ($messageTg['ok']) {
            $message = new PostMessage();
            $message->setMessageId($messageTg['result']['message_id']);
            $message->setPostId($postId);
            $message->setChannelId($channel_id);
            $message->setScheduleId($schedule_id);
            $this->messagesRepository->save($message);
            if ($schedule_id !== null) {
                $this->schedulesRepository->updateStatus($schedule_id, 'SENT');
            }
        } else {
            if ($schedule_id !== null) {
                $this->schedulesRepository->updateStatus($schedule_id, 'SENT_FAILED');
            }
            $this->logger->warning("Post $postId wasn't send", json_encode($messageTg, JSON_PRETTY_PRINT));
        }
    }

    public function deletePost(int $postId, int $schedule_id)
    {
        $message = $this->messagesRepository->getByScheduleId($schedule_id);
        $messageTg = TelegramClient::deleteMessage($message->getChannelId() ?? 0, $message->getMessageId() ?? 0);
        if (!$messageTg['ok']) {
            $this->schedulesRepository->updateStatus($schedule_id, 'DELETE_FAILED');
            $this->logger->warning("Message with schedule $schedule_id wasn't deleted", json_encode($messageTg, JSON_PRETTY_PRINT));
        } else {
            $this->schedulesRepository->updateStatus($schedule_id, 'DELETED');
        }
    }
}