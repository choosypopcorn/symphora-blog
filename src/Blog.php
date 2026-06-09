<?php

declare(strict_types=1);

namespace SymphoraBlog;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class Blog
{
    private PostRepository $repository;
    private Environment $twig;

    public function __construct(string $templatesDirectory, string $postsDirectory)
    {
        $this->repository = new PostRepository($postsDirectory, new MarkdownParser());
        $this->twig = new Environment(new FilesystemLoader($templatesDirectory));
    }

    public function renderIndex(): string
    {
        return $this->twig->render('index.html.twig', [
            'posts' => $this->repository->all(),
        ]);
    }

    public function renderPost(string $slug): ?string
    {
        $post = $this->repository->findBySlug($slug);
        if ($post === null) {
            return null;
        }

        return $this->twig->render('post.html.twig', [
            'post' => $post,
        ]);
    }
}
