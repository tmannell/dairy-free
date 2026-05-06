# Setup

## Prerequisites

- [Lando](https://lando.dev) — for local development
- [Composer](https://getcomposer.org)
- PHP 8.5+
- MariaDB 10+

---

## Local Development (Lando)

1. **Clone the repo**
   ```bash
   git clone git@github.com:tmannell/dairy-free.git
   cd dairy-free
   ```

2. **Start Lando**
   ```bash
   lando start
   ```
   This spins up PHP 8.5, MariaDB, and a test database container.

3. **Install dependencies**
   ```bash
   lando composer install
   ```

4. **Configure the app**
   ```bash
   cp config.ini.example config.ini
   ```
   Edit `config.ini` with your local database credentials. The Lando defaults are:
   ```ini
   database = "mysql:host=database;port=3306;dbname=lamp"
   username = "lamp"
   password = "lamp"
   ```

5. **Configure the test database**
   ```bash
   cp config.test.ini.example config.test.ini
   ```
   The test DB credentials match the `testdb` service in `.lando.yml`.

6. **Install the app**
   Navigate to `https://dairyfree.lndo.site/install` to create the database schema and admin user.

---

## Running Tests

```bash
lando php vendor/bin/phpunit
```

---

## Production Setup (LAMP)

1. **Server requirements**
   - PHP 8.5+ with PDO and MySQLi extensions
   - MariaDB 10+
   - Apache with `mod_rewrite` enabled

2. **Deploy files**
   Upload the repo contents to your webroot. The document root should point to the `web/` directory.

3. **Configure the app**
   ```bash
   cp config.ini.example config.ini
   ```
   Edit `config.ini` with your production database credentials and site settings.

4. **Configure the cron job**
   ```bash
   cp cron.ini.example cron.ini
   ```
   Edit `cron.ini` with the correct path to your webroot, then add a crontab entry:
   ```
   * 1 * * * cd /path/to/webroot; php cron.php /cron
   ```
   This runs `autoPublish` daily to publish any scheduled posts.

5. **Install the app**
   Navigate to `/install` on your production domain to create the database schema and admin user.