<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class RssController extends AbstractController
{
    /**
     * @Route("/rss", name="app_rss")
     */
    #[Route('/rss', name: 'app_rss')]
    public function generateRss(Environment $twig)
    {
        $posts = json_decode(file_get_contents(__DIR__ . '/../../../../config/storage.php')['posts'], true) ?: [];
        
        // Sort by date (latest first)
        usort($posts, function($a, $b) { return $a['date'] <=> $b['date']; });
        
        $feedContent = $twig->render('rss/feed.xml.twig', ['posts' => array_reverse($posts)]);
        
        return new Response(
            $feedContent,
            200,
            [
                'Content-Type' => 'application/rss+xml',
                'Cache-Control' => 'public, max-age=60'
            ]
        );
    }
}
