# dairy-free

A dairy-free CMS for those sensitive to lactose. Publish one image per day, an online endurance art piece.

What to post? A photo, a drawing, your aunt, or what you ate for dinner last night. 

Built for me, but it could be useful for you: customizable, simple, predictable, and easy to post from anywhere.

## Features

- Publish a single image per day
- Set publish dates in the future (work ahead)
- Simple admin workflow for uploads and captions
- Chronological archive of entries
- Customizable look and feel
- Minimal dependencies and straightforward data model
- Server-rendered (no frontend build step)

## Stack

- PHP (Fat-Free Framework)
- MariaDB
- F3 templating
- Bootstrap 4

## Requirements

- PHP 7.4+
- MariaDB 10+
- Composer
- Lando

## Local Setup

```bash
git clone git@github.com:tmannell/dairy-free.git
cd dairy-free
lando start
composer install
cp config.ini.example config.ini
# edit config.ini with your database credentials
```

Open the app using the provided URL (e.g. http://dairy-free.lndo.site)

## Usage

- On first run, navigate to `/install` to set up the database and create an admin account
- Log in and use the admin interface to upload an image and add a caption
- Set a publish date (defaults to today, or schedule ahead)