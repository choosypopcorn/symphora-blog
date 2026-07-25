<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class PostController extends AbstractController
{
    private string $storagePath;
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->storagePath = (require __DIR__ . '/../../config/storage.php')['posts'];
        $this->filesystem = new Filesystem();
    }

    #[Route('/', name: 'homepage')]
    public function homepage(): Response
    {
        $posts = [];

        if (file_exists($this->storagePath)) {
            $posts = json_decode(file_get_contents($this->storagePath), true) ?: [];
        }

        usort($posts, static function (array $left, array $right): int {
            $leftDate = $left['date'] ?? null;
            $rightDate = $right['date'] ?? null;

            if (is_object($leftDate) && method_exists($leftDate, 'getTimestamp')) {
                $leftDate = $leftDate->getTimestamp();
            }

            if (is_object($rightDate) && method_exists($rightDate, 'getTimestamp')) {
                $rightDate = $rightDate->getTimestamp();
            }

            if (is_string($leftDate)) {
                $leftDate = strtotime($leftDate);
            }

            if (is_string($rightDate)) {
                $rightDate = strtotime($rightDate);
            }

            if ($leftDate === $rightDate) {
                return 0;
            }

            if ($leftDate === null) {
                return 1;
            }

            if ($rightDate === null) {
                return -1;
            }

            return $leftDate < $rightDate ? 1 : -1;
        });

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST') && $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            // Handle AJAX request for markdown preview
            $content = $request->getPayload()->get('content');
            $converter = new GithubFlavoredMarkdownConverter();
            $html = $converter->convert($content)->getContent();
            return new Response($html);
        }

        // Display the create form
        return $this->render('post/create.html.twig');
    }

    #[Route('/save', name: 'save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        if (!$this->getUser()) {
            throw new AccessDeniedException('You must be logged in to create posts.');
        }

        $title = $request->getPayload()->get('title');
        $content = $request->getPayload()->get('content');

        if (!$title || !$content) {
            return new Response('Title and content are required.', 400);
        }

        // Parse markdown to HTML
        $converter = new GithubFlavoredMarkdownConverter();
        $htmlContent = $converter->convert($content)->getContent();

        // Handle image uploads
        $images = [];
        $uploadedFiles = $request->files->get('images', []);
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        foreach ($uploadedFiles as $file) {
            if ($file && $file->isValid()) {
                $filename = uniqid() . '_' . $file->getClientOriginalName();
                $file->move('uploads/images', $filename);
                $images[] = '/uploads/images/' . $filename;
            }
        }

        // Load existing posts
        $data = [];
        if (file_exists($this->storagePath)) {
            $data = json_decode(file_get_contents($this->storagePath), true) ?: [];
        }

        // Create new post
        $post = [
            'id' => uniqid(),
            'title' => $title,
            'content' => $htmlContent,
            'author' => $this->getUser()->getUsername(),
            'date' => date('Y-m-d H:i:s'),
            'images' => $images,
        ];

        // Add post to array
        $data[] = $post;

        // Save to file
        $this->filesystem->dumpFile($this->storagePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return new Response('Post saved successfully');
    }
}
