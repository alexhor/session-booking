# Session Booking
Built on CodeIgniter 4 Application Starter

## Installation
```
composer install --prefer-dist --no-progress
spark migrate --all
cd frontend
npm run build
```

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings.

## Server Requirements

PHP version 8.1 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

> [!WARNING]
> - The end of life date for PHP 7.4 was November 28, 2022.
> - The end of life date for PHP 8.0 was November 26, 2023.
> - If you are still using PHP 7.4 or 8.0, you should upgrade immediately.
> - The end of life date for PHP 8.1 will be December 31, 2025.

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

You can read the [user guide](https://codeigniter.com/user_guide/)
corresponding to the latest version of the framework.

## Dev Setup
Set `CI_ENVIRONMENT = development` in `.env` file.

In the `frontend` folder run `npm install` and then `npm run dev` to start the vite server.
You will have to route all websocket connections for path `/vite/wss` to the vite server as Codeigniter can't handle websocket.

For example this is how it can be done in apache:
```
<VirtualHost *:80>
    # Here goes you normal config
    ...

    # Handle Vite WebSocket connections
    <Location /vite/wss>
        ProxyPass "ws://localhost:5173/vite/wss"
        ProxyPassReverse "ws://localhost:5173/vite/wss"
    </Location>
</VirtualHost>
```
