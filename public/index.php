<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use SymphoraBlog\Blog;

$blog = new Blog(
    dirname(__DIR__) . '/templates',
    dirname(__DIR__) . '/posts',
);

$postSlug = $_GET['post'] ?? null;

if (is_string($postSlug) && $postSlug !== '') {
    $renderedPost = $blog->renderPost($postSlug);
    if ($renderedPost === null) {
        http_response_code(404);
        echo 'Post not found';
        return;
    }

    echo $renderedPost;
    return;
}

echo $blog->renderIndex();
