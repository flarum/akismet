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

class ValidatePost
{
    /**
     * @var Akismet
     */
    protected $akismet;

    public function __construct(Akismet $akismet)
    {
        $this->akismet = $akismet;
    }

    public function handle(Saving $event)
    {
        $post = $event->post;

        if ($post->exists || $post->user->hasPermission('bypassAkismet')) {
            return;
        }

        $this->akismet->setContent($post->content);
        $this->akismet->setAuthorName($post->user->username);
        $this->akismet->setAuthorEmail($post->user->email);
        $this->akismet->setType($post->number === 1 ? 'forum-post' : 'reply');
        $this->akismet->setIp($post->ip_address);
        //TODO
        //$this->akismet->setUserAgent(Arr::get($this->request->getServerParams(), 'HTTP_USER_AGENT'));

        if ($this->akismet->isSpam()) {
            $post->is_approved = false;
            $post->is_spam = true;

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
