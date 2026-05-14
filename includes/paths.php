<?php
const APP_BASE_URL = '/SMS/';

function app_url(string $path = ''): string
{
    if ($path === '' || $path === '/') {
        return APP_BASE_URL;
    }

    if (preg_match('/^(?:[a-z][a-z0-9+.-]*:|#)/i', $path)) {
        return $path;
    }

    return APP_BASE_URL . ltrim($path, '/');
}
