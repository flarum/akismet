<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Akismet\Listener;

use Flarum\Akismet\Akismet;
use Flarum\Approval\Event\PostWasApproved;

class SubmitHam
{
    /**
     * @var Akismet
     */
    protected $akismet;

    public function __construct(Akismet $akismet)
    {
        $this->akismet = $akismet;
    }

    public function handle(PostWasApproved $event)
    {
        $post = $event->post;

        if ($post->is_spam) {
            $this->akismet->setContent($post->content);
            $this->akismet->setIp($post->ip_address);
            $this->akismet->setAuthorName($post->user->username);
            $this->akismet->setAuthorEmail($post->user->email);
            $this->akismet->setType($post->number === 1 ? 'forum-post' : 'reply');

            $this->akismet->submitHam();
        }
    }
}
