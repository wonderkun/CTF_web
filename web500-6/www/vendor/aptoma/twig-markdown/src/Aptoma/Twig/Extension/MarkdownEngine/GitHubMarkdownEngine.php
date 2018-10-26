<?php
namespace Aptoma\Twig\Extension\MarkdownEngine;

use Aptoma\Twig\Extension\MarkdownEngineInterface;

/**
 * GithubMarkdownEngine.php
 *
 * Maps GitHub's Markdown engine API to Aptoma\Twig Markdown Extension using
 * KnpLabs\php-github-api.
 *
 * @author Lukas W <lukaswhl@gmail.com>
 */
class GitHubMarkdownEngine implements MarkdownEngineInterface
{
    /**
     * Constructor
     *
     * @param string $contextRepo The repository context. Pass a GitHub repo
     *        such as 'aptoma/twig-markdown' to render e.g. issues #23 in the
     *        context of the repo.
     * @param bool $gfm Whether to use GitHub's Flavored Markdown or the
     *        standard markdown. Default is true.
     * @param string $cacheDir Location on disk where rendered documents should
     *        be stored.
     * @param \Github\Client $client Client object to use. A new Github\Client()
     *        object is constructed automatically if $client is null.
     */
    public function __construct($contextRepo = null, $gfm = true, $cacheDir = '/tmp/github-markdown-cache', \GitHub\Client $client=null)
    {
        $this->repo = $contextRepo;
        $this->mode = $gfm ? 'gfm' : 'markdown';
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }

        if ($client === null) {
            $client = new \Github\Client();
        }
        $this->api = $client->api('markdown');
    }

    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        $cacheFile = $this->getCachePath($content);
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);;
        }

        $response = $this->api->render($content, $this->mode, $this->repo);
        file_put_contents($cacheFile, $response);
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'KnpLabs\php-github-api';
    }

    private function getCachePath($content)
    {
        return $this->cacheDir . md5($content) . '_' . $this->mode. '_' . str_replace('/', '.', $this->repo);
    }

    private $api;
    private $cacheDir;
    private $repo;
    private $mode;
}
