<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace Flarum\Akismet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Akismet
{
    private $apiUrl;
    private $flarumVersion;
    private $extensionVersion;

    private $params = [];

    public function __construct(string $apiKey, string $homeUrl, $flarumVersion, $extensionVersion, $inDebugMode = false)
    {
        $this->apiUrl = "https://$apiKey.rest.akismet.com/1.1";
        $this->setBlog($homeUrl);

        $this->flarumVersion = $flarumVersion;
        $this->extensionVersion = $extensionVersion;

        if ($inDebugMode) {
            $this->setTest();
        }
    }

    /**
     * @param  string  $type  eg. submit-spam, submit-spam or submit-ham;
     * @throws GuzzleException
     */
    public function sendRequest(string $type): ResponseInterface
    {
        $client = new Client();
        return $client->request('POST', "$this->apiUrl/$type", [
            'headers'     => [
                'User-Agent' => "Flarum/$this->flarumVersion | Akismet/$this->extensionVersion",
            ],
            'form_params' => $this->params,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function isSpam(): bool
    {
        $response = $this->sendRequest('comment-check');

        if ($response->getBody()->getContents() === 'true') {
            //TODO handle X-akismet-pro-tip header
            return true;
        }

        return false;
    }

    /**
     * @throws GuzzleException
     */
    public function submitSpam()
    {
        $this->sendRequest('submit-spam');
    }

    /**
     * @throws GuzzleException
     */
    public function submitHam()
    {
        $this->sendRequest('submit-ham');
    }

    /**
     * The front page or home URL of the instance making the request. For a blog or wiki this would be the front page. Note: Must be a full URI, including http://.
     */
    public function setBlog(string $url): Akismet
    {
        $this->params['blog'] = $url;
        return $this;
    }

    /**
     * IP address of the comment submitter.
     */
    public function setIp(string $ip): Akismet
    {
        $this->params['user_ip'] = $ip;
        return $this;
    }

    /**
     * User agent string of the web browser submitting the comment - typically the HTTP_USER_AGENT cgi variable. Not to be confused with the user agent of your Akismet library.
     */
    public function setUserAgent(string $userAgent): Akismet
    {
        $this->params['user_agent'] = $userAgent;
        return $this;
    }

    /**
     * The full permanent URL of the entry the comment was submitted to.
     */
    public function setPermalink(string $permalink): Akismet
    {
        $this->params['permalink'] = $permalink;
        return $this;
    }

    /**
     * A string that describes the type of content being sent
     * Examples:
     * comment: A blog comment.
     * forum-post: A top-level forum post.
     * reply: A reply to a top-level forum post.
     * blog-post: A blog post.
     * contact-form: A contact form or feedback form submission.
     * signup: A new user account.
     * message: A message sent between just a few users.
     */
    public function setType(string $type): Akismet
    {
        $this->params['comment_type'] = $type;
        return $this;
    }

    /**
     * Name submitted with the comment.
     */
    public function setAuthorName(string $name): Akismet
    {
        $this->params['comment_author'] = $name;
        return $this;
    }

    /**
     * Email address submitted with the comment.
     */
    public function setAuthorEmail(string $email): Akismet
    {
        $this->params['comment_author_email'] = $email;
        return $this;
    }

    /*
     * URL submitted with comment. Only send a URL that was manually entered by the user, not an automatically generated URL like the user’s profile URL on your site.
     */
    public function setAuthorUrl(string $url): Akismet
    {
        $this->params['comment_author_url'] = $url;
        return $this;
    }

    /**
     * The content that was submitted.
     */
    public function setContent(string $content): Akismet
    {
        $this->params['comment_content'] = $content;
        return $this;
    }

    /**
     * Indicates the language(s) in use on the blog or site, in ISO 639-1 format, comma-separated. A site with articles in English and French might use “en, fr_ca”.
     */
    public function setLanguage(string $language): Akismet
    {
        $this->params['blog_lang'] = $language;
        return $this;
    }

    /**
     * This is an optional parameter. You can use it when submitting test queries to Akismet.
     */
    public function setTest(): Akismet
    {
        $this->params['is_test'] = true;
        return $this;
    }


    public function setParams(array $params): Akismet
    {
        if (!empty($params)) {
            $this->params = array_merge($this->params, $params);
        }
        return $this;
    }
}

