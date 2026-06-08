# symphora-blog

A simple blogging system built with:

- PHP
- Twig templates
- Markdown posts stored in the filesystem
- Git for version control of content and code

## Project structure

```text
posts/       Markdown blog posts (with frontmatter metadata)
public/      Web entrypoint (`index.php`)
src/         Blog, post repository, and parsing logic
templates/   Twig templates
```

## Getting started

```bash
composer install
php -S localhost:8000 -t public
```

Open <http://localhost:8000>.

## Post format

Posts use Markdown with YAML frontmatter:

```markdown
---
title: My First Blog Post
date: 2026-06-07
author: Symphora
tags: [php, markdown, twig]
slug: my-first-post
---

# My First Blog Post

Post content...
```

## Tests

This repository currently does not include an automated test suite.
