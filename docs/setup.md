# Setup

## Prerequisites

- [DDEV](https://ddev.com) — for local development
- [Composer](https://getcomposer.org)
- PHP 8.5+
- MariaDB 10+

## Local Development (DDEV)

1. **Clone the repo**
   ```bash
   git clone git@github.com:tmannell/dairy-free.git
   cd dairy-free
   ```

2. **Start DDEV**
   ```bash
   ddev start
   ```
   This starts PHP 8.5, MariaDB 11.8 and nginx. A `post-start` hook in `.ddev/config.yaml` creates the `dairyfree_test` database for PHPUnit.
     
3. **Install dependencies**
   ```bash
   ddev composer install
   ```

4. **Configure the app**
   ```bash
   cp config.ini.example config.ini
   ```
   Edit `config.ini` with DDEV database defaults.
   ```ini
   database = "mysql:host=db;port=3306;dbname=db"
   username = "db"
   password = "db"
   ```

5. **Configure the test database**
   
   The test config (`config.test.ini`) is committed and points at DDEV.

6. **Install the app**
   Go to `https://dairy-free.ddev.site/install` to create the database schema and admin user.


## Running Tests

```bash
ddev exec php vendor/bin/phpunit
```
Run this from project root, because the autoload paths in `config.test.ini` are relative to it.


## Debugging (Xdebug)

Xdebug is off by default. Toggle it with `ddev xdebug on` / `ddev xdebug off`, or keep it on locally by adding the following to a git-ignored `.ddev/config.local.yaml`:

```yaml
xdebug_enabled: true
```

See the [DDEV step debugging docs](https://docs.ddev.com/en/stable/users/debugging-profiling/step-debugging/) for editor setup.


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