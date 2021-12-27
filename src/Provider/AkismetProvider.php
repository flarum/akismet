<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Akismet\Provider;

use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Foundation\Config;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Akismet\Akismet;

class AkismetProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->container->bind(Akismet::class, function () {
            /** @var SettingsRepositoryInterface $settings */
            $settings = $this->container->make(SettingsRepositoryInterface::class);
            /** @var UrlGenerator $url */
            $url = $this->container->make(UrlGenerator::class);
            /** @var Config $config */
            $config = $this->container->make(Config::class);

            return new Akismet(
                $settings->get('flarum-akismet.api_key'),
                $url->to('forum')->base(),
                $config->inDebugMode()
            );
        });
    }
}
