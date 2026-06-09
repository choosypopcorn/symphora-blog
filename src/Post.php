<?php

declare(strict_types=1);

namespace SymphoraBlog;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final class Post
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public readonly string $title,
        public readonly DateTimeImmutable $date,
        public readonly string $slug,
        public readonly ?string $author,
        public readonly array $tags,
        public readonly string $content,
    ) {
    }

    public static function fromFile(string $path, MarkdownParser $markdownParser): self
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Unable to read post: {$path}");
        }

        $metadata = [];
        $markdown = $raw;
        if (preg_match('/\A---\R(.*?)\R---\R(.*)\z/s', $raw, $matches) === 1) {
            $metadata = Yaml::parse($matches[1]) ?? [];
            $markdown = $matches[2];
        }

        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $title = isset($metadata['title']) && is_string($metadata['title'])
            ? trim($metadata['title'])
            : ucwords(str_replace('-', ' ', $fileName));
        $slug = isset($metadata['slug']) && is_string($metadata['slug']) ? trim($metadata['slug']) : $fileName;
        $author = isset($metadata['author']) && is_string($metadata['author']) ? trim($metadata['author']) : null;

        $tags = [];
        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            foreach ($metadata['tags'] as $tag) {
                if (is_string($tag) && trim($tag) !== '') {
                    $tags[] = trim($tag);
                }
            }
        }

        $dateValue = $metadata['date'] ?? null;
        $date = self::parseDate($dateValue, (int) filemtime($path));

        return new self(
            $title,
            $date,
            $slug,
            $author,
            $tags,
            $markdownParser->toHtml($markdown),
        );
    }

    private static function parseDate(mixed $dateValue, int $fallbackTimestamp): DateTimeImmutable
    {
        if (is_string($dateValue)) {
            try {
                $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $dateValue)
                    ?: new DateTimeImmutable($dateValue);
                if ($parsed instanceof DateTimeImmutable) {
                    return $parsed;
                }
            } catch (\Exception) {
                // fall back to file timestamp
            }
        }

        return (new DateTimeImmutable())->setTimestamp($fallbackTimestamp);
    }
}
