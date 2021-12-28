<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Akismet\Listener;

use Flarum\Akismet\Akismet;
use Flarum\Flags\Flag;
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
        //If no API key is provided in the extension settings, then there is no point in validating the post.
        if (!$this->akismet->isConfigured) {
            return;
        }

        $post = $event->post;

        //TODO Sometimes someone posts spam when editing a post. In this 'recheck_reason=edit' can be used when sending a request to Akismet
        if ($post->exists || $post->user->hasPermission('bypassAkismet')) {
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
                $post->hidden_at = time();

                $post->afterSave(function ($post) {
                    if ($post->number == 1) {
                        $post->discussion->hidden_at = time();
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
                    $flag->created_at = time();

                    $flag->save();
                });
            }
        }
    }
}
