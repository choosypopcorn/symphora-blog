<?php

declare(strict_types=1);

namespace SymphoraBlog;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

final class PostRepository
{
    public function __construct(
        private readonly string $postsDirectory,
        private readonly MarkdownParser $markdownParser,
    ) {
    }

    /**
     * @return list<Post>
     */
    public function all(): array
    {
        if (!is_dir($this->postsDirectory)) {
            return [];
        }

        $posts = [];
        foreach ($this->postFiles() as $filePath) {
            $post = Post::fromFile($filePath, $this->markdownParser);
            $posts[] = $post;
        }

        usort(
            $posts,
            static fn (Post $left, Post $right): int => $right->date->getTimestamp() <=> $left->date->getTimestamp(),
        );

        return $posts;
    }

    public function findBySlug(string $slug): ?Post
    {
        if (preg_match('/^[a-z0-9][a-z0-9-]*$/i', $slug) !== 1) {
            return null;
        }

        foreach ($this->postFiles() as $filePath) {
            if (pathinfo($filePath, PATHINFO_FILENAME) !== $slug) {
                continue;
            }

            $post = Post::fromFile($filePath, $this->markdownParser);
            if ($post->slug === $slug) {
                return $post;
            }
        }

        return null;
    }

    /**
     * @return iterable<string>
     */
    private function postFiles(): iterable
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->postsDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            ),
            '/^.+\.md$/i',
        );

        foreach ($iterator as $file) {
            yield (string) $file;
        }
    }
}
