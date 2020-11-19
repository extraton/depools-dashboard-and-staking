<?php namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    private string $siteName;
    private string $domain;

    public function __construct(
        string $siteName,
        string $domain
    )
    {
        $this->siteName = $siteName;
        $this->domain = $domain;
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
}
