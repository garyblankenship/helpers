# PHP Helper Functions

This repository contains a collection of helpful PHP functions designed to be highly reusable and flexible, especially for Laravel developers who need to work with non-Laravel, plain PHP code.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
  - [Request & Response](#request--response)
    - [request](#request)
    - [response](#response)
    - [json_response](#json_response)
    - [redirect](#redirect)
    - [abort](#abort)
  - [Debugging & Logging](#debugging--logging)
    - [dd](#dd)
    - [dump](#dump)
    - [info](#info)
    - [logger](#logger)
  - [Arrays](#arrays)
    - [array_only](#array_only)
    - [array_except](#array_except)
    - [array_pluck](#array_pluck)
    - [data_get](#data_get)
    - [data_set](#data_set)
  - [Session & Authentication](#session--authentication)
    - [session](#session)
    - [auth](#auth)
    - [old](#old)
  - [Security](#security)
    - [encrypt](#encrypt)
    - [decrypt](#decrypt)
    - [csrf_field](#csrf_field)
    - [method_field](#method_field)
    - [e](#e)
  - [Configuration & Environment](#configuration--environment)
    - [env](#env)
    - [config](#config)
    - [server](#server)
  - [URLs & Assets](#urls--assets)
    - [url](#url)
    - [asset](#asset)
    - [route](#route)
  - [Views & Storage](#views--storage)
    - [view](#view)
    - [storage](#storage)
  - [Caching](#caching)
    - [cache](#cache)
  - [Utilities](#utilities)
    - [value](#value)
    - [dispatch](#dispatch)
    - [lastline](#lastline)
- [License](#license)

## Installation

To include these functions in your project, simply download or clone this repository and include the `helpers.php` file in your project:

```php
require_once 'path/to/helpers.php';
```

## Usage

### Request & Response

#### request

Retrieve the value of a request variable from `$_REQUEST`.

```php
// Get a single value
$name = request('name'); // Returns null if not set
$name = request('name', 'Guest'); // Returns 'Guest' if not set

// Get multiple values at once
$data = request(['name', 'email', 'age']); 
// Returns: ['name' => 'John', 'email' => 'john@example.com', 'age' => null]

// Get all request data
$allData = request(); // Returns entire $_REQUEST array

// Working with arrays
$items = request('items', []); // Default to empty array
$firstItem = request('items.0'); // Get first item using dot notation
```

#### response

Create an HTTP response with custom content, status code, and headers.

```php
// Simple text response
response('Hello, World!'); // 200 OK by default

// Custom status code
response('Not Found', 404);

// With custom headers
response('File content here', 200, [
    'Content-Type' => 'text/plain',
    'Content-Disposition' => 'attachment; filename="file.txt"'
]);

// Empty response with status
response('', 204); // No Content
```

#### json_response

Create a JSON HTTP response.

```php
// Simple JSON response
json_response(['success' => true]);

// With custom status code
json_response(['error' => 'Not found'], 404);

// Complex data structure
json_response([
    'user' => [
        'id' => 123,
        'name' => 'John Doe',
        'roles' => ['admin', 'user']
    ],
    'meta' => [
        'total' => 100,
        'page' => 1
    ]
], 200);

// With custom headers
json_response(['data' => 'value'], 200, [
    'X-Custom-Header' => 'value'
]);
```

#### redirect

Redirect to a specified URL.

```php
// Simple redirect
redirect('https://example.com');

// Redirect to relative path
redirect('/login');

// Redirect with custom status code
redirect('/new-location', 301); // Permanent redirect

// Redirect back (using HTTP_REFERER)
redirect($_SERVER['HTTP_REFERER'] ?? '/');
```

#### abort

Abort the request with a specified status code and message.

```php
// Simple abort
abort(404); // Shows "404 Not Found"

// With custom message
abort(403, 'Access Denied');

// Common uses
if (!$authorized) {
    abort(401, 'Unauthorized');
}

if (!$resource) {
    abort(404, 'Resource not found');
}

// Server error
abort(500, 'Internal Server Error');
```

### Debugging & Logging

#### dd

Dump the given value and die (stop execution).

```php
// Debug a variable
$user = ['name' => 'John', 'age' => 30];
dd($user); // Outputs formatted data and stops execution

// Debug multiple values
dd($user, $request, $config);

// Debug in a condition
if ($debugging) {
    dd('Debug point reached', $data);
}
```

#### dump

Dump the given value without stopping execution.

```php
// Debug a variable
dump($user); // Outputs formatted data
dump($request); // Continues execution

// Debug in a loop
foreach ($items as $item) {
    dump($item);
    // Process continues...
}

// Multiple dumps
dump('Step 1', $data1);
// ... more code ...
dump('Step 2', $data2);
```

#### info

Log an informational message.

```php
// Simple message
info('User logged in successfully');

// With context data
info('User action performed', [
    'user_id' => 123,
    'action' => 'update_profile',
    'timestamp' => time()
]);

// Log important events
info('Payment processed', [
    'order_id' => 'ORD-123',
    'amount' => 99.99,
    'currency' => 'USD'
]);
```

#### logger

Log a message with specified level.

```php
// Different log levels
logger('info', 'Application started');
logger('warning', 'Deprecated function used', ['function' => 'oldMethod']);
logger('error', 'Database connection failed', ['host' => 'localhost']);
logger('debug', 'Variable state', ['data' => $debugData]);

// Critical error logging
logger('critical', 'System failure', [
    'error' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString()
]);
```

### Arrays

#### array_only

Get a subset of the items from the given array.

```php
// Basic usage
$user = ['name' => 'John', 'age' => 30, 'email' => 'john@example.com', 'password' => 'secret'];
$public = array_only($user, ['name', 'email']);
// Returns: ['name' => 'John', 'email' => 'john@example.com']

// Single key
$nameOnly = array_only($user, 'name');
// Returns: ['name' => 'John']

// Non-existent keys are ignored
$subset = array_only($user, ['name', 'phone']);
// Returns: ['name' => 'John']

// Numeric arrays
$numbers = ['zero', 'one', 'two', 'three'];
$subset = array_only($numbers, [0, 2]);
// Returns: [0 => 'zero', 2 => 'two']
```

#### array_except

Get all of the given array except for a specified array of keys.

```php
// Basic usage
$user = ['name' => 'John', 'age' => 30, 'email' => 'john@example.com', 'password' => 'secret'];
$safe = array_except($user, ['password']);
// Returns: ['name' => 'John', 'age' => 30, 'email' => 'john@example.com']

// Multiple keys
$filtered = array_except($user, ['password', 'age']);
// Returns: ['name' => 'John', 'email' => 'john@example.com']

// Single key as string
$withoutEmail = array_except($user, 'email');
// Returns: ['name' => 'John', 'age' => 30, 'password' => 'secret']

// Numeric arrays
$numbers = ['zero', 'one', 'two', 'three'];
$filtered = array_except($numbers, [1, 3]);
// Returns: [0 => 'zero', 2 => 'two']
```

#### array_pluck

Pluck an array of values from an array.

```php
// Basic usage
$users = [
    ['name' => 'John', 'age' => 30, 'role' => 'admin'],
    ['name' => 'Jane', 'age' => 25, 'role' => 'user'],
    ['name' => 'Bob', 'age' => 35, 'role' => 'user']
];
$names = array_pluck($users, 'name');
// Returns: ['John', 'Jane', 'Bob']

// With key parameter
$namesByRole = array_pluck($users, 'name', 'role');
// Returns: ['admin' => 'John', 'user' => 'Bob'] (last user overwrites)

// Using numeric keys
$namesByAge = array_pluck($users, 'name', 'age');
// Returns: [30 => 'John', 25 => 'Jane', 35 => 'Bob']

// Nested arrays with dot notation
$data = [
    ['user' => ['name' => 'John', 'id' => 1], 'active' => true],
    ['user' => ['name' => 'Jane', 'id' => 2], 'active' => false]
];
$names = array_pluck($data, 'user.name');
// Returns: ['John', 'Jane']

$namesByIds = array_pluck($data, 'user.name', 'user.id');
// Returns: [1 => 'John', 2 => 'Jane']

// Working with objects
$objects = [
    (object)['name' => 'John', 'id' => 1],
    (object)['name' => 'Jane', 'id' => 2]
];
$names = array_pluck($objects, 'name');
// Returns: ['John', 'Jane']
```

#### data_get

Get a value from an array or object using dot notation.

```php
// Basic array access
$data = ['user' => ['name' => 'John', 'age' => 30]];
$name = data_get($data, 'user.name'); // Returns: 'John'
$age = data_get($data, 'user.age'); // Returns: 30

// Default values
$phone = data_get($data, 'user.phone', 'N/A'); // Returns: 'N/A'

// Nested arrays
$config = [
    'database' => [
        'mysql' => [
            'host' => 'localhost',
            'port' => 3306
        ]
    ]
];
$host = data_get($config, 'database.mysql.host'); // Returns: 'localhost'

// Working with objects
$obj = (object)['name' => 'John', 'details' => (object)['age' => 30]];
$age = data_get($obj, 'details.age'); // Returns: 30

// Array of keys
$value = data_get($data, ['user', 'name']); // Returns: 'John'
```

#### data_set

Set a value within an array or object using dot notation.

```php
// Basic usage
$data = [];
data_set($data, 'user.name', 'John');
// $data is now: ['user' => ['name' => 'John']]

// Adding to existing array
$data = ['user' => ['age' => 30]];
data_set($data, 'user.name', 'John');
// $data is now: ['user' => ['age' => 30, 'name' => 'John']]

// Deep nesting
$config = [];
data_set($config, 'database.mysql.host', 'localhost');
data_set($config, 'database.mysql.port', 3306);
// $config is now: ['database' => ['mysql' => ['host' => 'localhost', 'port' => 3306]]]

// Working with objects
$obj = new stdClass();
data_set($obj, 'user.name', 'John');
// $obj->user->name is now 'John'

// Overwriting values
$data = ['user' => ['name' => 'Jane']];
data_set($data, 'user.name', 'John');
// $data is now: ['user' => ['name' => 'John']]
```

### Session & Authentication

#### session

Get or set session values.

```php
// Get a session value
$username = session('username');
$userId = session('user_id', 0); // Default to 0 if not set

// Set session values
session(['username' => 'john_doe']);
session(['user_id' => 123, 'role' => 'admin']);

// Check if session key exists
if (session('is_logged_in')) {
    // User is logged in
}

// Complex session data
session([
    'cart' => [
        'items' => [
            ['id' => 1, 'qty' => 2],
            ['id' => 2, 'qty' => 1]
        ],
        'total' => 99.99
    ]
]);
$cartTotal = session('cart.total'); // Using dot notation
```

#### auth

Get the authenticated user.

```php
// Get current user
$user = auth();

if ($user) {
    echo "Welcome, " . $user['name'];
} else {
    echo "Please log in";
}

// Check authentication
if (auth()) {
    // User is authenticated
} else {
    redirect('/login');
}

// Access user properties
$userId = auth()['id'] ?? null;
$userEmail = auth()['email'] ?? null;
```

#### old

Retrieve an old input value (typically used after form validation failure).

```php
// In a form after validation error
<input type="text" name="username" value="<?= e(old('username')) ?>">
<input type="email" name="email" value="<?= e(old('email', 'default@example.com')) ?>">

// With select options
<select name="country">
    <option value="us" <?= old('country') === 'us' ? 'selected' : '' ?>>United States</option>
    <option value="uk" <?= old('country') === 'uk' ? 'selected' : '' ?>>United Kingdom</option>
</select>

// Checkbox
<input type="checkbox" name="remember" <?= old('remember') ? 'checked' : '' ?>>

// Textarea
<textarea name="message"><?= e(old('message')) ?></textarea>
```

### Security

#### encrypt

Encrypt the given value using OpenSSL.

```php
// Encrypt sensitive data
$encrypted = encrypt('my-secret-password');
// Returns base64 encoded encrypted string

// Encrypt user data
$encryptedSSN = encrypt('123-45-6789');
$encryptedCC = encrypt('4111-1111-1111-1111');

// Store encrypted data
$user['payment_info'] = encrypt(json_encode([
    'card_number' => '4111111111111111',
    'exp_date' => '12/25'
]));

// Encrypt with custom key (set APP_KEY in environment)
$_SERVER['APP_KEY'] = 'your-32-character-key-here!!!!!';
$encrypted = encrypt('sensitive data');
```

#### decrypt

Decrypt the given value.

```php
// Decrypt data
$encrypted = encrypt('my-secret');
$decrypted = decrypt($encrypted); // Returns: 'my-secret'

// Handle decryption failure
try {
    $data = decrypt($encryptedData);
} catch (Exception $e) {
    // Handle invalid or tampered data
    logger('error', 'Decryption failed', ['error' => $e->getMessage()]);
}

// Decrypt and parse JSON
$encryptedJson = encrypt(json_encode(['user' => 'john', 'role' => 'admin']));
$data = json_decode(decrypt($encryptedJson), true);
// $data = ['user' => 'john', 'role' => 'admin']

// Verify decryption worked
$original = 'test-data';
$encrypted = encrypt($original);
$decrypted = decrypt($encrypted);
assert($original === $decrypted);
```

#### csrf_field

Generate a CSRF token field for forms.

```php
// In an HTML form
<form method="POST" action="/submit">
    <?= csrf_field() ?>
    <input type="text" name="username">
    <button type="submit">Submit</button>
</form>

// Generates:
// <input type="hidden" name="_token" value="[random-token]">

// Manual token access
$token = session('_token');

// Verify CSRF token in handler
if (request('_token') !== session('_token')) {
    abort(403, 'CSRF token mismatch');
}
```

#### method_field

Generate a hidden input field for HTTP method spoofing.

```php
// PUT request
<form method="POST" action="/users/123">
    <?= csrf_field() ?>
    <?= method_field('PUT') ?>
    <input type="text" name="name">
    <button type="submit">Update</button>
</form>

// DELETE request
<form method="POST" action="/users/123">
    <?= csrf_field() ?>
    <?= method_field('DELETE') ?>
    <button type="submit">Delete User</button>
</form>

// PATCH request
<form method="POST" action="/posts/456">
    <?= method_field('PATCH') ?>
    <textarea name="content"></textarea>
    <button type="submit">Update Post</button>
</form>
```

#### e

Escape HTML special characters to prevent XSS attacks.

```php
// Basic escaping
$userInput = '<script>alert("XSS")</script>';
echo e($userInput); 
// Outputs: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;

// In HTML attributes
<input type="text" value="<?= e($userInput) ?>">
<div title="<?= e($tooltip) ?>">Hover me</div>

// Safe output in views
<h1>Welcome, <?= e($username) ?>!</h1>
<p><?= e($userBio) ?></p>

// Handling null values
echo e(null); // Returns empty string
echo e(''); // Returns empty string

// Complex example
$comment = 'Check this <a href="javascript:alert(1)">link</a> & "quote"';
echo e($comment); 
// Outputs: Check this &lt;a href=&quot;javascript:alert(1)&quot;&gt;link&lt;/a&gt; &amp; &quot;quote&quot;
```

### Configuration & Environment

#### env

Retrieve the value of an environment variable.

```php
// Get environment variables
$appEnv = env('APP_ENV', 'production'); // Default to 'production'
$debug = env('APP_DEBUG', false); // Default to false
$appName = env('APP_NAME', 'My Application');

// Database configuration
$dbHost = env('DB_HOST', 'localhost');
$dbPort = env('DB_PORT', 3306);
$dbName = env('DB_DATABASE', 'myapp');

// API keys and secrets
$apiKey = env('API_KEY'); // Returns null if not set
$secretKey = env('SECRET_KEY', ''); // Empty string default

// Boolean values
$isDebug = env('DEBUG', false);
$isMaintenanceMode = env('MAINTENANCE_MODE', false);

// Using in configuration
$config = [
    'mail' => [
        'driver' => env('MAIL_DRIVER', 'smtp'),
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
    ]
];
```

#### config

Retrieve the value of a configuration setting.

```php
// Get configuration values
$dbHost = config('database.host', 'localhost');
$appName = config('app.name', 'My App');
$timezone = config('app.timezone', 'UTC');

// Nested configuration
$mysqlHost = config('database.connections.mysql.host');
$cacheDriver = config('cache.default', 'file');

// Set configuration values
config(['app.debug' => true]);
config([
    'database.connections.mysql.host' => '127.0.0.1',
    'database.connections.mysql.port' => 3306
]);

// Get entire configuration array
$dbConfig = config('database'); // Returns entire database config array

// Check if configuration exists
if (config('services.stripe.key')) {
    // Stripe is configured
}
```

#### server

Get a value from the `$_SERVER` superglobal.

```php
// Common server variables
$host = server('HTTP_HOST'); // example.com
$method = server('REQUEST_METHOD'); // GET, POST, etc.
$uri = server('REQUEST_URI'); // /path/to/page
$userAgent = server('HTTP_USER_AGENT');
$ip = server('REMOTE_ADDR');

// With defaults
$https = server('HTTPS', 'off');
$port = server('SERVER_PORT', 80);
$serverName = server('SERVER_NAME', 'localhost');

// Request headers
$contentType = server('CONTENT_TYPE');
$acceptLanguage = server('HTTP_ACCEPT_LANGUAGE', 'en');
$referer = server('HTTP_REFERER', '/');

// Server information
$software = server('SERVER_SOFTWARE'); // Apache/2.4.41
$protocol = server('SERVER_PROTOCOL'); // HTTP/1.1
$docRoot = server('DOCUMENT_ROOT'); // /var/www/html
```

### URLs & Assets

#### url

Generate a URL for the application.

```php
// Basic URL generation
echo url('products'); // http://example.com/products
echo url('products/123'); // http://example.com/products/123
echo url('/auth/login'); // http://example.com/auth/login

// With query parameters
echo url('search', ['q' => 'laravel', 'page' => 2]); 
// http://example.com/search?q=laravel&page=2

echo url('users', ['sort' => 'name', 'order' => 'asc']);
// http://example.com/users?sort=name&order=asc

// Force HTTPS
echo url('checkout', [], true); // https://example.com/checkout
echo url('api/secure', ['token' => 'abc123'], true);
// https://example.com/api/secure?token=abc123

// Empty path returns base URL
echo url(''); // http://example.com
echo url('', [], true); // https://example.com

// Complex query parameters
echo url('filter', [
    'category' => ['electronics', 'computers'],
    'price' => ['min' => 100, 'max' => 500]
]);
// http://example.com/filter?category[0]=electronics&category[1]=computers&price[min]=100&price[max]=500
```

#### asset

Generate a URL for an asset using the current scheme of the request.

```php
// Basic asset URLs
echo asset('css/app.css'); // http://example.com/css/app.css
echo asset('js/app.js'); // http://example.com/js/app.js
echo asset('images/logo.png'); // http://example.com/images/logo.png

// Force HTTPS
echo asset('css/app.css', true); // https://example.com/css/app.css

// With subdirectories
echo asset('vendor/bootstrap/css/bootstrap.min.css');
// http://example.com/vendor/bootstrap/css/bootstrap.min.css

// In HTML
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script src="<?= asset('js/script.js') ?>"></script>
<img src="<?= asset('images/header.jpg') ?>" alt="Header">

// Versioned assets
echo asset('css/app.css?v=1.2.3');
// http://example.com/css/app.css?v=1.2.3

// Dynamic assets
$theme = 'dark';
echo asset("css/theme-{$theme}.css");
// http://example.com/css/theme-dark.css
```

#### route

Handle routing for the current request.

```php
// Define routes and handle the request
$routes = [
    'GET' => [
        '/' => 'HomeController@index',
        '/about' => 'PageController@about',
        '/users' => 'UserController@index',
    ],
    'POST' => [
        '/users' => 'UserController@store',
        '/login' => 'AuthController@login',
    ]
];

// The route() function will handle the current request
route(); // Automatically routes based on REQUEST_METHOD and REQUEST_URI

// Example usage in index.php
require_once 'helpers.php';
require_once 'routes.php';

// Handle the request
route();
```

### Views & Storage

#### view

Render a view template with data.

```php
// Basic view rendering
view('welcome'); // Renders views/welcome.php

// Pass data to view
view('profile', ['name' => 'John', 'email' => 'john@example.com']);

// In views/profile.php:
// <h1>Welcome, <?= e($name) ?>!</h1>
// <p>Email: <?= e($email) ?></p>

// With merge data
$defaultData = ['theme' => 'light', 'lang' => 'en'];
view('dashboard', ['user' => $user], $defaultData);

// Nested views
view('admin/users', ['users' => $users]);
// Renders views/admin/users.php

// Complex data structures
view('products', [
    'products' => [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999.99],
        ['id' => 2, 'name' => 'Mouse', 'price' => 29.99]
    ],
    'currency' => 'USD',
    'user' => auth()
]);

// Using both extracted variables and $viewData array
// In view file:
// <?= e($name) ?> <!-- Direct variable access -->
// <?= e($viewData['name']) ?> <!-- Array access (backward compatible) -->
```

#### storage

Get the path to the storage directory.

```php
// Get storage directory path
$storagePath = storage(); // /path/to/document/root/storage

// Get specific file/directory paths
$uploadsPath = storage('uploads'); // /path/to/document/root/storage/uploads
$logFile = storage('logs/app.log'); // /path/to/document/root/storage/logs/app.log

// Create upload directory
$uploadDir = storage('uploads/images');
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Save uploaded file
$uploadPath = storage('uploads/' . $_FILES['file']['name']);
move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath);

// Read from storage
$data = file_get_contents(storage('data/config.json'));
$logs = file(storage('logs/error.log'));

// Write to storage
file_put_contents(storage('cache/data.json'), json_encode($data));
file_put_contents(storage('logs/custom.log'), $logMessage, FILE_APPEND);

// Check if file exists in storage
if (file_exists(storage('uploads/document.pdf'))) {
    // Serve the file
}
```

### Caching

#### cache

Simple file-based cache helper for storing and retrieving data.

```php
// Store data in cache
cache('user_123', $userData, 3600); // Cache for 1 hour
cache('settings', $settings, 86400); // Cache for 24 hours
cache('permanent_data', $data, 0); // Cache forever (no expiration)

// Retrieve from cache
$user = cache('user_123'); // Returns null if not found or expired
$settings = cache('settings', []); // Returns empty array as default

// Cache expensive operations
$products = cache('all_products');
if ($products === null) {
    $products = fetchProductsFromDatabase(); // Expensive operation
    cache('all_products', $products, 1800); // Cache for 30 minutes
}

// Clear specific cache
cache('user_123', null, -1); // Setting negative TTL removes the cache

// Cache with dynamic keys
$userId = 123;
$cacheKey = "user_profile_{$userId}";
$profile = cache($cacheKey, null, 7200); // 2 hours

// Cache API responses
$apiData = cache('api_weather_data');
if (!$apiData) {
    $apiData = fetchWeatherFromAPI();
    cache('api_weather_data', $apiData, 600); // 10 minutes
}
```

### Utilities

#### value

Return the default value of the given value (resolves closures).

```php
// Basic usage
$result = value('hello'); // Returns: 'hello'
$result = value(123); // Returns: 123

// Resolve closures
$result = value(function () {
    return 'computed value';
}); // Returns: 'computed value'

// Conditional logic in closures
$user = ['type' => 'admin'];
$access = value(function () use ($user) {
    return $user['type'] === 'admin' ? 'full' : 'limited';
}); // Returns: 'full'

// Lazy evaluation
$expensive = value(function () {
    // Only executed when value() is called
    return performExpensiveOperation();
});

// Default value patterns
$default = value($userInput ?? function () {
    return calculateDefault();
});

// With callbacks
$items = [1, 2, 3];
$result = value(function () use ($items) {
    return array_sum($items);
}); // Returns: 6
```

#### dispatch

Dispatch a job or callable (placeholder for job queue functionality).

```php
// Dispatch a callable
dispatch(function () {
    sendWelcomeEmail('user@example.com');
});

// Dispatch with parameters
dispatch(function ($userId) {
    processUserData($userId);
}, 123);

// Dispatch a job class name
dispatch('ProcessOrderJob', ['order_id' => 456]);

// Async task simulation
dispatch(function () {
    // Long running task
    generateReport();
    cleanupTempFiles();
});

// With error handling
dispatch(function () {
    try {
        syncDataWithAPI();
    } catch (Exception $e) {
        logger('error', 'Sync failed', ['error' => $e->getMessage()]);
    }
});
```

#### lastline

Get the last line of a file (useful for reading log files).

```php
// Get last line of a log file
$lastError = lastline('/var/log/app/error.log');
$lastAccess = lastline('/var/log/nginx/access.log');

// Check last log entry
$logPath = storage('logs/application.log');
$lastEntry = lastline($logPath);
echo "Last log: " . $lastEntry;

// Monitor file changes
$lastLine = lastline('data.txt');
// ... after some time ...
$newLastLine = lastline('data.txt');
if ($lastLine !== $newLastLine) {
    echo "File has been updated!";
}

// Parse last CSV line
$csvFile = 'exports/data.csv';
$lastRow = lastline($csvFile);
$data = str_getcsv($lastRow);

// Get last error from PHP error log
$errorLog = ini_get('error_log');
$lastPhpError = lastline($errorLog);
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.