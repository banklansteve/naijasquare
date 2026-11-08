<?php

namespace App\Community;

use App\Models\AppNotification;
use App\Models\Post;

class NotifyMatchingOfferAuthors
{
    public const TYPE = 'related_need';

    public function __construct(private FindRelatedOffers $relatedOffers) {}

    public function handle(Post $need): int
    {
        if ($need->type !== Post::TYPE_NEED) {
            return 0;
        }

        $need->loadMissing(['community:id,slug,name', 'author:id,name']);

        $authors = $this->relatedOffers->authorsToNotify($need);
        $created = 0;

        foreach ($authors as $author) {
            $alreadyNotified = AppNotification::query()
                ->where('user_id', $author->id)
                ->where('type', self::TYPE)
                ->where('data->need_id', $need->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            AppNotification::query()->create([
                'user_id' => $author->id,
                'type' => self::TYPE,
                'data' => [
                    'need_id' => $need->id,
                    'need_title' => $need->title,
                    'community_id' => $need->community_id,
                    'community_slug' => $need->community?->slug,
                    'community_name' => $need->community?->name,
                    'category_key' => $need->category_key,
                    'category_label' => $need->categoryLabel(),
                    'author_name' => $need->author?->name,
                    'href' => '/communities/'.$need->community?->slug.'/posts/'.$need->id,
                ],
            ]);

            $created++;
        }

        return $created;
    }
}
