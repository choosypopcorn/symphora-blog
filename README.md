# Symphora Blog

A simple Symfony-based blog application that lets you create and view posts, plus expose an RSS feed.

## Features

- Homepage that lists saved posts
- Post creation form
- Simple RSS feed at `/rss`
- Post storage via JSON files in the `data` directory

## Requirements

- PHP 8.4+
- Composer
- A local web server or Symfony server

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Start the Symfony development server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

## Usage

- Open `http://127.0.0.1:8000/` to view the homepage
- Visit `http://127.0.0.1:8000/create` to access the post creation view
- Visit `http://127.0.0.1:8000/rss` to view the RSS feed

## Project Structure

- `src/Controller` — application controllers
- `templates/` — Twig templates
- `config/` — Symfony configuration and route setup
- `data/` — stored blog content

## Notes

Posts are currently stored in JSON files configured in `config/storage.php`.
