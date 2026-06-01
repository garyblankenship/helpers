# PHP Helpers

A single-file helper collection for plain PHP 8.1+ projects that want a few Laravel-style conveniences without installing a framework.

Drop in `helpers.php`, require it from your bootstrap, and use the helpers you need for requests, responses, config, sessions, views, storage, caching, arrays, encryption, and small control-flow niceties.

## Why This Exists

Plain PHP is still a good fit for small sites, prototypes, admin tools, webhooks, and legacy code that does not need a full framework. This file gives those projects a familiar vocabulary:

```php
require_once __DIR__ . '/helpers.php';

config([
    'base_url' => 'https://example.com',
    'storage_path' => __DIR__ . '/../storage',
]);

dispatch();
```

No Composer package is required. No service container is required. The helpers are guarded with `function_exists()` so they can live alongside other code.

## Requirements

- PHP 8.1 or newer
- OpenSSL for `encrypt()` / `decrypt()`
- Sessions enabled if you use `session()`, `csrf_field()`, `old()`, or `auth()`

## Installation

Copy `helpers.php` into your project and require it from your bootstrap or entry point:

```php
require_once __DIR__ . '/helpers.php';
```

For encryption, provide an `APP_KEY` of at least 32 bytes through the environment or a `.env` file in the document root:

```env
APP_KEY=change-me-to-a-random-32-byte-secret
```

## A Small App

This is the intended scale: a tiny front controller with readable handlers.

```php
<?php

require_once __DIR__ . '/helpers.php';

config([
    'base_url' => 'https://example.com',
    'storage_path' => __DIR__ . '/../storage',
]);

function getIndex(): void
{
    view('home', [
        'title' => 'Dashboard',
        'user' => auth(),
    ]);
}

function postProfile(): void
{
    if (request('_token') !== session('_token')) {
        abort(403, 'CSRF token mismatch');
    }

    $name = trim((string) request('name'));

    logger('info', 'Profile updated', ['name' => $name]);

    session(['_old_input' => []]);
    redirect('/profile');
}

dispatch();
```

And a matching view:

```php
<!-- views/home.php -->
<h1><?= e($title) ?></h1>

<form method="POST" action="/profile">
    <?= csrf_field() ?>
    <input name="name" value="<?= e(old('name', $user['name'] ?? '')) ?>">
    <button type="submit">Save</button>
</form>
```

## Recipes

### Request Data

```php
$name = request('name', 'Guest');
$firstItem = request('items.0');

$data = request(['name', 'email']);
// ['name' => ..., 'email' => ...]

$all = request();
```

`request()` reads from `$_REQUEST` and supports dot notation through `data_get()`.

### Responses

```php
response('Created', 201);

json_response([
    'ok' => true,
    'id' => 123,
]);

redirect('/login');

abort(404, 'Not found');
```

### Configuration And Environment

```php
// config.php in the document root may return an array.
$timezone = config('app.timezone', 'UTC');

config([
    'app.debug' => true,
    'cache_path' => storage('cache'),
]);

$debug = env('APP_DEBUG', false);
$host = server('HTTP_HOST', 'localhost');
```

### Arrays And Dot Notation

```php
$user = [
    'name' => 'Ada',
    'profile' => ['email' => 'ada@example.com'],
    'password' => 'secret',
];

$email = data_get($user, 'profile.email');
$public = array_except($user, 'password');
$only = array_only($user, ['name', 'profile']);

data_set($user, 'profile.timezone', 'UTC');
```

### Sessions And Forms

```php
session([
    'user' => ['id' => 7, 'name' => 'Ada'],
    '_old_input' => request(),
]);

$user = auth();
$previousEmail = old('email');
```

```php
<form method="POST" action="/users/7">
    <?= csrf_field() ?>
    <?= method_field('PATCH') ?>
    <input name="email" value="<?= e(old('email')) ?>">
    <button type="submit">Update</button>
</form>
```

### Views, Storage, And Logs

```php
view('reports/show', [
    'report' => $report,
]);

$path = storage('reports/today.json');
file_put_contents($path, json_encode($report));

logger('info', 'Report written', ['path' => $path]);
echo lastline(storage('logs/' . date('Y-m-d') . '.log'));
```

`view()` resolves files inside `DOCUMENT_ROOT/views` and exposes both extracted variables and `$viewData`.

### Cache

```php
$products = cache('products');

if ($products === null) {
    $products = fetchProducts();
    cache('products', $products, 1800);
}
```

The file cache stores JSON payloads under `config('cache_path')` or `storage('cache')`.

### URLs, Assets, And Routes

```php
echo url('search', ['q' => 'php helpers']);
echo asset('css/app.css');

$GLOBALS['routes'] = [
    'home' => '/',
    'user.show' => '/users/{id}',
];

echo route('user.show', ['id' => 7]);
```

### Encryption

```php
$token = encrypt(['user_id' => 7, 'scope' => 'invite']);
$payload = decrypt($token);
```

Encrypted values are authenticated with an HMAC. Tampered payloads throw an exception during decryption.

### Utility Flow

```php
$settings = once('settings', function () {
    return config('settings', []);
});

$profile = retry(3, function (int $attempt) {
    return fetchProfileFromApi();
}, 250);

$user = tap(auth(), function ($user) {
    logger('info', 'User loaded', ['id' => $user['id'] ?? null]);
});

$fallback = value(function () {
    return 'computed only when needed';
});
```

## Helper Reference

### Request & Response

| Helper | Purpose |
| --- | --- |
| `request($key = null, $default = null)` | Read request data, including dot-notated keys. |
| `response($content = '', $status = 200, array $headers = [])` | Send an HTTP response body, status, and headers. |
| `json_response($data, $status = 200, array $headers = [])` | Send a JSON response. |
| `redirect(string $url, int $status = 302)` | Send a redirect response and exit. |
| `abort(int $code, string $message = '')` | Send an error status, escaped message, and exit. |
| `dispatch()` | Route the current request to functions like `getIndex()` or `postUser()`. |

### Debugging & Logging

| Helper | Purpose |
| --- | --- |
| `dd(...$data)` | Dump values and stop execution. |
| `dump(...$data)` | Dump values and continue execution. |
| `info(string $message, array $context = [])` | Log an info-level message. |
| `logger(string $level, string $message, array $context = [])` | Append a log entry under configured log storage. |
| `lastline(string $filePath)` | Return the last line from a file. |

### Arrays

| Helper | Purpose |
| --- | --- |
| `array_only(array $array, array|string $keys)` | Keep only selected keys. |
| `array_except(array $array, array|string $keys)` | Remove selected keys. |
| `array_pluck(array $array, array|string $value, array|string|null $key = null)` | Extract values from a list, optionally keyed by another value. |
| `data_get($target, array|string $key, $default = null)` | Read nested arrays, objects, and `ArrayAccess` values. |
| `data_set(&$target, array|string $key, $value, bool $overwrite = true)` | Write nested arrays and objects. |

### Session & Security

| Helper | Purpose |
| --- | --- |
| `session($key = null, $default = null)` | Start and read/write the PHP session. |
| `auth()` | Return `session('user')`. |
| `old(string $key, $default = null)` | Read `_old_input` values from the session. |
| `csrf_field()` | Generate a hidden `_token` input and session token. |
| `method_field(string $method)` | Generate a hidden `_method` input. |
| `e(?string $value)` | Escape HTML output. |
| `encrypt($value, bool $serialize = true)` | Encrypt and authenticate a value. |
| `decrypt(string $payload, bool $unserialize = true)` | Decrypt and verify an encrypted payload. |

### Configuration, Paths, And URLs

| Helper | Purpose |
| --- | --- |
| `env(string $key, $default = null)` | Read `.env`, `$_ENV`, or process environment values. |
| `config($key = null, $default = null)` | Read or set cached configuration values. |
| `server(string $key, $default = null)` | Read `$_SERVER` with a default. |
| `storage(string $path = '')` | Build a path under configured storage. |
| `view(string $view, array $data = [], array $mergeData = [])` | Render a PHP view from the views directory. |
| `url(?string $path = null, array $parameters = [], ?bool $secure = null)` | Build an absolute URL from the current request host. |
| `asset(string $path)` | Build an asset URL using `base_url` config when available. |
| `route(string $name, array $parameters = [])` | Fill placeholders in `$GLOBALS['routes']`. |
| `cache($key = null, $value = null, ?int $seconds = null)` | Read or write JSON file cache entries. |

### Utilities

| Helper | Purpose |
| --- | --- |
| `value($value)` | Return a value, resolving closures lazily. |
| `once(string $key, callable $callback)` | Run a callback once per key for the current request. |
| `retry(int $times, callable $callback, int $sleepMilliseconds = 0)` | Retry a throwing callback before rethrowing the final exception. |
| `tap($value, callable $callback)` | Run a side effect and return the original value. |

## Notes

- This library intentionally keeps behavior small and explicit.
- It is useful for lightweight apps, scripts, prototypes, and legacy projects that want a familiar helper layer.
- It is not a replacement for a framework router, validator, ORM, queue, or dependency injection container.

## License

MIT. See [LICENSE](LICENSE).
