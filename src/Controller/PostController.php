<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use League\Commonmark\Parser;
use Symfony\Component\Filesystem\Filesystem;

class PostController extends AbstractController
{
    private $storagePath;

    public function __construct()
    {
        $this->storagePath = (require __DIR__ . '/../../config/storage.php')['posts'];
        $this->filesystem = new Filesystem();
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // Handle AJAX request for markdown preview
            return new Response(Parser::parse($request->get('content'))); 
        }
        
        return $this->render('post/create.html.twig');
    }

    /**
     * @Route("/save", name="save", methods={"POST"})
     */
    public function save(Request $request)
    {
        if (!$this->getUser()) {
            throw new AccessDeniedException();
        }

        $data = json_decode(file_get_contents($this->storagePath), true) ?: [];
        $post = [
            'title' => $request->get('title'),
            'content' => $request->get('content'),
            'author' => $this->getUser()-> getUsername(),
            'date' => new \DateTime(),
            'images' => $request->get('images', []) // Array of image paths
        ];

        if (isset($post['id'])) {
            // Update existing post
            foreach ($data as &$item) {
                if ($item['id'] == $post['id']) {
                    $item = array_merge($item, $post);
                    break;
                }
            }
        } else {
            // Generate new ID and add post
            static::$ids ??= [];
            do {
                $newId = random_int(1000, 9999);
            } while (in_array($newId, array_column($data, 'id')) && isset(static::$ids[$newId]));
            
            $post['id'] = $newId;
            static::$ids[$newId] = true; // Simple in-memory tracking
        }

        $this->filesystem->dumpFile($this->storagePath, json_encode(array_merge($data, [$post]), JSON_PRETTY_PRINT));
        return new Response('Post saved successfully');
    }
}
