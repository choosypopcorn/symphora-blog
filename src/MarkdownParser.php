<?php

declare(strict_types=1);

namespace SymphoraBlog;

use Parsedown;

final class MarkdownParser
{
    private Parsedown $parser;

    public function __construct()
    {
        $this->parser = new Parsedown();
        $this->parser->setSafeMode(true);
    }

    public function toHtml(string $markdown): string
    {
        $html = $this->parser->text($markdown);

        return strip_tags(
            $html,
            '<p><br><a><strong><em><ul><ol><li><blockquote><pre><code><h1><h2><h3><h4><h5><h6><hr>',
        );
    }
}
