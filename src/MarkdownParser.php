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
        return $this->parser->text($markdown);
    }
}
