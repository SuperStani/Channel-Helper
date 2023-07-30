<?php


namespace App\Core\Controllers\Telegram\Query;


use App\Core\Controllers\Telegram\QueryController;
use App\Core\Controllers\Telegram\UserController;
use App\Core\Logger\LoggerInterface;
use App\Core\ORM\Repositories\ChannelsRepository;
use App\Core\ORM\Repositories\PostsRepository;
use App\Core\ORM\Repositories\SchedulesRepository;
use App\Integrations\Telegram\Query;

class SchedulesController extends QueryController
{
    private PostsRepository $postsRepository;
    private SchedulesRepository $schedulesRepository;
    private ChannelsRepository $channelsRepository;

    public function __construct(
        Query $query,
        UserController $user,
        LoggerInterface $logger,
        PostsRepository $postsRepository,
        SchedulesRepository $schedulesRepository,
        ChannelsRepository $channelsRepository
    )
    {
        parent::__construct($query, $user, $logger);
        $this->postsRepository = $postsRepository;
        $this->schedulesRepository = $schedulesRepository;
        $this->channelsRepository = $channelsRepository;
    }

    public function home(?int $offset = 0): ?array
    {
        $posts = $this->postsRepository->getPostsNotProcessedYet(10, $offset);
        $text = get_string('scheduled_posts');
        $x = $y = 0;
        $emojis = ["1️⃣", "2️⃣", "3️⃣", "4️⃣", "5️⃣", "6️⃣", "7️⃣", "8️⃣", "9️⃣", "🔟"];
        foreach($posts as $key => $post) {
            if($x < 5) {
                $x++;
            } else {
                $y++;
                $x = 1;
            }
            $channelTitle = $this->channelsRepository->getById($post->getSchedule()->getChannelId())->getTitle();
            $menu[$y][] = ["text" => $emojis[$key] . get_button('delete'), "callback_data" => "Schedules:delete|" . $post->getSchedule()->getId()];
            $text .= "\n" . $emojis[$key] . " » " . ($post->getCaption() ?? '🖼') . " (" . ($post->getSchedule()->getDatetimeSend() ?? 'Non impostato') . " - " . ($post->getSchedule()->getDatetimeEnd() ?? 'Non impostato') . " ) *$channelTitle*";
        }
        $menu[] = [["text" => get_button('menu'), "callback_data" => "Home:start"]];
        return $this->query->message->edit($text, $menu);
    }

    public function delete(int $scheduleId): ?array
    {
        $this->schedulesRepository->deleteById($scheduleId);
        return $this->home();
    }
}