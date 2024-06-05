# PHP Helper Functions

This repository contains a collection of helpful PHP functions designed to be highly reusable and flexible, especially for Laravel developers who need to work with non-Laravel, plain PHP code.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
  - [request](#request)
  - [dd](#dd)
  - [dump](#dump)
  - [env](#env)
  - [config](#config)
  - [redirect](#redirect)
  - [asset](#asset)
  - [old](#old)
  - [csrf_field](#csrf_field)
  - [method_field](#method_field)
  - [route](#route)
  - [auth](#auth)
  - [abort](#abort)
  - [session](#session)
  - [server](#server)
  - [data_get](#data_get)
  - [value](#value)
  - [cache](#cache)
  - [data_set](#data_set)
  - [encrypt](#encrypt)
  - [decrypt](#decrypt)
  - [info](#info)
  - [logger](#logger)
  - [response](#response)
  - [json_response](#json_response)
  - [storage](#storage)
  - [url](#url)
  - [view](#view)
  - [array_pluck](#array_pluck)
  - [array_except](#array_except)
  - [array_only](#array_only)
- [License](#license)

## Installation

To include these functions in your project, simply download or clone this repository and include the `functions.php` file in your project:

```php
require_once 'path/to/functions.php';
```

## Usage

### request

Retrieve the value of a request variable from `$_REQUEST`.

```php
$value = request('items'); // if items is not set, $value will be null
$value = request('items', 'default value'); // if items is not set, $value will be 'default value'
$value = request(['items', 'other']); // if items is not set, $value will be ['items' => null, 'other' => null]
```

### dd

Dump the given value and die.

```php
dd($myVariable); // Dumps the variable and stops execution
```

### dump

Dump the given value.

```php
dump($myVariable); // Outputs the contents of $myVariable in a readable format
```

### env

Retrieve the value of an environment variable.

```php
$appEnv = env('APP_ENV', 'production'); // Gets the value of APP_ENV or 'production' if not set
```

### config

Retrieve the value of a configuration setting.

```php
$dbHost = config('database.host', 'localhost'); // Gets the value of database.host or 'localhost' if not set
```

### redirect

Redirect to a specified URL.

```php
redirect('https://example.com'); // Redirects to the specified URL
```

### asset

Generate a URL for an asset.

```php
$assetUrl = asset('images/logo.png'); // Generates the URL for the asset
```

### old

Retrieve an old input value.

```php
$oldValue = old('username'); // Gets the old input value for 'username'
```

### csrf_field

Generate a CSRF token field.

```php
echo csrf_field(); // Outputs the CSRF token field
```

### method_field

Generate a hidden input field for the HTTP method.

```php
echo method_field('PUT'); // Outputs the hidden input field for the PUT method
```

### route

Handle routing for the request.

```php
route(); // Handles the routing for the request
```

### auth

Get the authenticated user.

```php
$user = auth(); // Gets the authenticated user
```

### abort

Abort the request with a specified status code and message.

```php
abort(404, 'Not Found'); // Aborts the request with a 404 status code and message
```

### session

Get or set session values.

```php
$value = session('key'); // Gets the session value for 'key'
session(['key' => 'value']); // Sets the session value for 'key'
```

### server

Get a value from the `$_SERVER` superglobal.

```php
$host = server('HTTP_HOST'); // Gets the value of HTTP_HOST from the $_SERVER superglobal
```

### data_get

Get a value from an array or object using dot notation.

```php
$value = data_get($array, 'key'); // Gets the value of 'key' from the array
```

### value

Return the default value of the given value.

```php
$default = value($someValue); // Returns the default value of $someValue
```

### cache

Simple cache helper function to store and retrieve data from a file-based cache.

```php
cache('my_key', 'my_value', 1800); // Cache 'my_value' for 1800 seconds (30 minutes)
$value = cache('my_key'); // Retrieve the cached value
```

### data_set

Set a value within an array or object using dot notation.

```php
data_set($array, 'key', 'value'); // Sets the value of 'key' in the array
```

### encrypt

Encrypt the given value.

```php
$encrypted = encrypt('my_secret_value'); // Returns the encrypted string
```

### decrypt

Decrypt the given value.

```php
$decrypted = decrypt($encryptedValue); // Returns the decrypted value
```

### info

Log an informational message.

```php
info('User logged in', ['user_id' => 123]); // Logs the message with context
```

### logger

Log a message.

```php
logger('info', 'User logged in', ['user_id' => 123]); // Logs the message with context
```

### response

Create an HTTP response.

```php
response('Hello, World!', 200, ['Content-Type' => 'text/plain']); // Creates a response
```

### json_response

Create a JSON HTTP response.

```php
json_response(['success' => true, 'message' => 'Data saved successfully'], 200); // Creates a JSON response
```

### storage

Get the storage path.

```php
$path = storage('uploads/myfile.txt'); // Returns the full path to the specified file in the storage directory
```

### url

Generate a URL.

```php
echo url('user/profile'); // Returns the full URL to the user profile page
echo url('search', ['q' => 'laravel']); // Returns the URL with query parameters
echo url('user/profile', [], true); // Returns a secure HTTPS URL to the user profile page
```

### view

Render a view template.

```php
view('home', ['name' => 'John']); // Renders the 'home' view with the provided data
```

### array_pluck

Pluck an array of values from an array.

```php
$array = [
  ['name' => 'John', 'age' => 30],
  ['name' => 'Jane', 'age' => 25],
];
$names = array_pluck($array, 'name'); // Returns ['John', 'Jane']
```

### array_except

Get all of the given array except for a specified array of keys.

```php
$array = ['name' => 'John', 'age' => 30, 'location' => 'NY'];
$result = array_except($array, ['age', 'location']); // Returns ['name' => 'John']
```

### array_only

Get a subset of the items from the given array.

```php
$array = ['name' => 'John', 'age' => 30, 'location' => 'NY'];
$result = array_only($array, ['name', 'location']); // Returns ['name' => 'John', 'location' => 'NY']
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.