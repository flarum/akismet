<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Akismet\Listener;

use Carbon\Carbon;
use Flarum\Akismet\Akismet;
use Flarum\Flags\Flag;
use Flarum\Post\CommentPost;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;

class ValidatePost
{
    /**
     * @var Akismet
     */
    protected $akismet;
    /**
     * @var SettingsRepositoryInterface
     */
    private $settings;

    public function __construct(Akismet $akismet, SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
        $this->akismet = $akismet;
    }

    public function handle(Saving $event)
    {
        if (!$this->akismet->isConfigured()) {
            return;
        }

        $post = $event->post;

        //TODO Sometimes someone posts spam when editing a post. In this case 'recheck_reason=edit' can be used when sending a request to Akismet
        if ($post->exists || !($post instanceof CommentPost) || $post->user->hasPermission('bypassAkismet')) {
            return;
        }

        $this->akismet
            ->setContent($post->content)
            ->setAuthorName($post->user->username)
            ->setAuthorEmail($post->user->email)
            ->setType($post->number == 1 ? 'forum-post' : 'reply')
            ->setIp($post->ip_address)
            ->setUserAgent($_SERVER['HTTP_USER_AGENT']);

        if ($this->akismet->isSpam()) {
            $post->is_spam = true;

            if ($this->akismet->proTip === 'discard' && $this->settings->get('flarum-akismet.delete_blatant_spam')) {
                $post->hide();

                $post->afterSave(function ($post) {
                    if ($post->number == 1) {
                        $post->discussion->hide();
                    }
                });
            } else {
                $post->is_approved = false;

                $post->afterSave(function ($post) {
                    if ($post->number == 1) {
                        $post->discussion->is_approved = false;
                        $post->discussion->save();
                    }

                    $flag = new Flag;

                    $flag->post_id = $post->id;
                    $flag->type = 'akismet';
                    $flag->created_at = Carbon::now();

                    $flag->save();
                });
            }
        }
    }
}
