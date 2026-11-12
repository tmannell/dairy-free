# dairy-free

A dairy-free CMS for those sensitive to lactose. Publish one image per day, an online endurance art piece.

What to post? A photo, a drawing, your aunt, or what you ate for dinner last night.

Built for me, but it could be useful for you: customizable, simple, predictable, and easy to post from anywhere.

## Features

- Publish a single image per day
- Set publish dates in the future (work ahead)
- Simple admin workflow for uploads and captions
- Chronological archive of entries
- Tag-based grouping and navigation
- Auto-publish scheduled posts via cron
- Customizable look and feel
- Minimal dependencies and straightforward data model

## Stack

- PHP 8.5 (Fat-Free Framework v3.8)
- MariaDB
- F3 templating
- Bootstrap 5

## Requirements

- PHP 8.5+
- MariaDB 10+
- Composer
- [DDEV](https://ddev.com) (for local development; provides PHP, MariaDB and Composer)

## Local Setup
```bash
git clone git@github.com:tmannell/dairy-free.git
cd dairy-free
ddev start
ddev composer install
cp config.ini.example config.ini
# set host=db, dbname=db, username/password=db
```

Open the app at https://dairy-free.ddev.site

## Docs

- [Setup](docs/setup.md) — local and production setup

## Contributing

- Branch off `main`
- Name branches `feature/`, `bug/`, or `maintenance/`
- PR back to `main` with a clear description — all PRs require review and approval before merging
- PHPUnit test suite — run with `ddev exec vendor/bin/phpunit`

## Roadmap

See [milestones](../../milestones) for planned work.
