<?php namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends AbstractController
{
    public const CACHE_DEPOOLS = 'depools';

    private string $siteName;
    private string $domain;
    private string $cacheDir;

    public function __construct(
        string $siteName,
        string $domain,
        string $cacheDir
    )
    {
        $this->siteName = $siteName;
        $this->domain = $domain;
        $this->cacheDir = $cacheDir;
    }

    public function index()
    {
        $jsConfig = [
            'siteName' => $this->siteName,
            'domain' => $this->domain,
        ];
        $data = [
            'jsConfig' => addslashes(json_encode($jsConfig, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE)),
            'frontDevPort' => '8099',
            'styles' => [],
            'scripts' => [],
        ];

        return $this->render('app.html.twig', $data);
    }

    public function depools()
    {
        $cache = new FilesystemAdapter('', 0, $this->cacheDir);
        $cacheDepools = $cache->getItem(IndexController::CACHE_DEPOOLS);
        $data = $cacheDepools->get() ?? [];

        return new JsonResponse($data);
    }
}
