<?php

if ( ! function_exists('request')) {
    /**
     * Get the value of a request variable from $_REQUEST.
     *
     * @param mixed|null $key
     * @param mixed|null $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $value = request('items'); // if items is not set, $value will be null
     * $value = request('items', 'default value'); // if items is not set, $value will be 'default value'
     * $value = request(['items', 'other']); // if items is not set, $value will be ['items' => null, 'other' => null]
     * </code>
     */
    function request(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_REQUEST;
        }

        if (is_array($key)) {
            $results = [];
            foreach ($key as $k) {
                $results[$k] = data_get($_REQUEST, $k, $default);
            }

            return $results;
        }

        return data_get($_REQUEST, $key, $default);
    }
}

if ( ! function_exists('dd')) {
    /**
     * Dump the given value and die.
     *
     * @param mixed $data
     *
     * @return void
     *
     * @example
     * <code>
     * dd($myVariable); // Dumps the variable and stops execution
     * </code>
     */
    function dd(...$data): void
    {
        foreach ($data as $value) {
            echo '<pre>';
            var_dump($value);
            echo '</pre>';
        }
        die();
    }
}

if ( ! function_exists('dump')) {
    /**
     * Dump the given values.
     *
     * @param mixed ...$data The values to dump.
     *
     * @return void
     *
     * @example
     * <code>
     * dump($myVariable); // Outputs the contents of $myVariable in a readable format
     * dump($var1, $var2, $var3); // Outputs multiple variables
     * </code>
     */
    function dump(...$data): void
    {
        foreach ($data as $value) {
            echo '<pre>';
            var_dump($value);
            echo '</pre>';
        }
    }
}

if ( ! function_exists('env')) {
    /**
     * Get the value of an environment variable from a .env file.
     *
     * This function reads environment variables from a .env file located in the document root.
     * The values are cached after the first read for better performance.
     *
     * Usage:
     * 1. Ensure you have a .env file in your document root.
     *    The .env file should contain key-value pairs, each on a new line, like this:
     *    APP_ENV=production
     *    DB_HOST=localhost
     *    DB_USER=root
     *    DB_PASS=secret
     *
     * 2. Use the env() function to retrieve environment variables:
     *    $appEnv = env('APP_ENV', 'production'); // Returns 'production' if APP_ENV is not set
     *    $databaseHost = env('DB_HOST', '127.0.0.1'); // Returns '127.0.0.1' if DB_HOST is not set
     *
     * @param string     $key     The key of the environment variable.
     * @param mixed|null $default The default value to return if the environment variable is not set.
     *
     * @return mixed The value of the environment variable or the default value.
     */
    function env(string $key, mixed $default = null): mixed
    {
        static $env = null;

        if ($env === null) {
            $env = [];
            $envFilePath = realpath(server('DOCUMENT_ROOT') . '/.env');
            if ($envFilePath && file_exists($envFilePath)) {
                $envFile = @fopen($envFilePath, 'r');
                if ($envFile) {
                    while (($line = fgets($envFile)) !== false) {
                        $line = trim($line);
                        if ($line && $line[0] !== '#' && str_contains($line, '=')) {
                            list($name, $value) = explode('=', $line, 2);
                            $name = trim($name);
                            $value = trim($value);
                            $env[$name] = $value;
                            $_ENV[$name] = $value;
                        }
                    }
                    fclose($envFile);
                }
            }
        }

        if (isset($env[$key])) {
            return $env[$key];
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if ( ! function_exists('config')) {
    /**
     * Get the value of a configuration setting.
     *
     * This function reads configuration settings from a config.php file located in the document root.
     * The configuration values are cached after the first read for better performance.
     *
     * Usage:
     * 1. Ensure you have a config.php file in your document root.
     *    The config.php file should return an associative array of configuration settings, like this:
     *    <?php
     *    return [
     *        'database' => [
     *            'host' => 'localhost',
     *            'user' => 'root',
     *            'pass' => 'secret',
     *        ],
     *        'app' => [
     *            'env' => 'production',
     *            'debug' => true,
     *        ],
     *    ];
     *
     * 2. Use the config() function to retrieve configuration settings:
     *    $dbHost = config('database.host', 'localhost'); // Returns 'localhost' if database.host is not set
     *    $appEnv = config('app.env', 'production'); // Returns 'production' if app.env is not set
     *
     * @param mixed|null $key     The key of the configuration setting, using dot notation for nested settings.
     * @param mixed|null $default The default value to return if the configuration setting is not set.
     *
     * @return mixed The value of the configuration setting or the default value.
     */
    function config(mixed $key = null, mixed $default = null): mixed
    {
        static $config = null;
        
        // Reset mechanism for testing - use special key '__RESET__'
        if ($key === '__RESET__') {
            $config = null;
            return null;
        }
        
        // Initialize once from global state and config file
        if ($config === null) {
            $config = $GLOBALS['app_config'] ?? [];
            $configFilePath = server('DOCUMENT_ROOT', sys_get_temp_dir()) . '/config.php';
            if (file_exists($configFilePath)) {
                $fileConfig = include $configFilePath;
                $config = array_merge($config, $fileConfig ?: []);
            }
        }

        // Set mode: write-through to both static and global
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                data_set($config, $k, $v);
            }
            $GLOBALS['app_config'] = $config;
            return null;
        }

        // Get mode: read from static cache only
        if ($key === null) {
            return $config;
        }

        return data_get($config, $key, $default);
    }
}

if ( ! function_exists('redirect')) {
    /**
     * Redirect to a specified URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The HTTP status code (default: 302)
     *
     * @return void
     *
     * @example
     * <code>
     * redirect('https://example.com'); // Redirects with 302 status
     * redirect('https://example.com', 301); // Permanent redirect
     * redirect('/login', 303); // See other redirect
     * </code>
     */
    function redirect(string $url, int $status = 302): void
    {
        header("Location: $url", true, $status);
        exit();
    }
}

if ( ! function_exists('asset')) {
    /**
     * Generate a URL for an asset.
     *
     * @param string $path
     *
     * @return string
     *
     * @example
     * <code>
     * $assetUrl = asset('images/logo.png'); // Generates the URL for the asset
     * </code>
     */
    function asset(string $path): string
    {
        $baseUrl = config('base_url');
        if ($baseUrl === null) {
            return url($path);
        }

        $baseUrl = rtrim($baseUrl, '/');
        $path = ltrim($path, '/');
        
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}

if ( ! function_exists('old')) {
    /**
     * Retrieve an old input value.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $oldValue = old('username'); // Gets the old input value for 'username'
     * </code>
     */
    function old(string $key, mixed $default = null): mixed
    {
        return session('_old_input.' . $key, $default);
    }
}

if ( ! function_exists('csrf_field')) {
    /**
     * Generate a CSRF token field.
     *
     * @return string
     *
     * @example
     * <code>
     * echo csrf_field(); // Outputs the CSRF token field
     * </code>
     */
    function csrf_field(): string
    {
        $token = session('_token');
        if (!is_string($token) || $token === '') {
            $legacyToken = session('csrf_token');
            $token = is_string($legacyToken) && $legacyToken !== '' ? $legacyToken : bin2hex(random_bytes(32));
            session(['_token' => $token, 'csrf_token' => $token]);
        } elseif (session('csrf_token') !== $token) {
            session(['csrf_token' => $token]);
        }

        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if ( ! function_exists('method_field')) {
    /**
     * Generate a hidden input field for the HTTP method.
     *
     * @param string $method
     *
     * @return string
     *
     * @example
     * <code>
     * echo method_field('PUT'); // Outputs the hidden input field for the PUT method
     * </code>
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . htmlspecialchars(strtoupper($method), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if ( ! function_exists('dispatch')) {
    /**
     * Dispatch the current HTTP request to a handler function.
     *
     * This function handles request dispatching by matching the HTTP method and URI to a corresponding function.
     * It dynamically constructs the function name based on the HTTP method and the first segment of the URI path.
     *
     * Example Usage:
     * 1. Define request handler functions:
     *    - The function name should be a combination of the HTTP method and the first URI segment.
     *      For example, for a GET request to '/user/123', the function should be named 'getUser'.
     *
     *    // Handles GET requests to /user/{id}
     *    function getUser($id) {
     *        // Handle GET request for user with ID $id
     *        echo "Handling GET request for user with ID: $id";
     *    }
     *
     *    // Handles POST requests to /user/{id}
     *    function postUser($id) {
     *        // Handle POST request for user with ID $id
     *        echo "Handling POST request for user with ID: $id";
     *    }
     *
     *    // Handles GET requests to / (root)
     *    function getIndex() {
     *        echo "Home page";
     *    }
     *
     * 2. Call the dispatch() function to handle the incoming request:
     *    dispatch(); // Matches the HTTP method and URI to the appropriate function
     *
     * How It Works:
     * 1. It retrieves the HTTP method of the request (e.g., GET, POST) and the requested URI path.
     * 2. For root path (/), it calls {method}Index function.
     * 3. For other paths, it constructs the function name by combining the lowercase HTTP method and the capitalized URI segment.
     * 4. All URI segments after the first are passed as separate parameters to the handler function.
     * 5. If no matching function is found, it aborts with a 404 status code.
     *
     * @return void
     *
     * @example
     * <code>
     * // Define request handler functions
     *
     * // Handles GET requests to /
     * function getIndex() {
     *     echo "Home page";
     * }
     *
     * // Handles GET requests to /user/{id}
     * function getUser($id) {
     *     echo "Handling GET request for user with ID: $id";
     * }
     *
     * // Handles GET requests to /user/{id}/edit
     * function getUser($id, $action) {
     *     echo "Handling GET request for user $id, action: $action";
     * }
     *
     * // Call the dispatch function to handle the request
     * dispatch();
     * </code>
     */
    function dispatch(): void
    {
        $requestedMethod = $_SERVER['REQUEST_METHOD'];
        $requestedPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        // Handle root path
        if (empty($requestedPath)) {
            $functionToCall = strtolower($requestedMethod) . 'Index';
            if (function_exists($functionToCall)) {
                call_user_func($functionToCall);
                return;
            }
        } else {
            // Handle normal paths
            $segments = explode('/', $requestedPath);
            $resource = array_shift($segments);
            $functionToCall = strtolower($requestedMethod) . ucfirst($resource);
            
            if (function_exists($functionToCall)) {
                call_user_func_array($functionToCall, $segments);
                return;
            }
        }

        abort(404, "Not Found");
    }
}

if ( ! function_exists('route')) {
    /**
     * Generate a URL for a named route.
     *
     * This function generates URLs based on route names and parameters.
     * Route patterns are defined in the global $routes array.
     *
     * Usage:
     * 1. Define routes in global scope or config:
     *    $GLOBALS['routes'] = [
     *        'home' => '/',
     *        'user.show' => '/user/{id}',
     *        'user.edit' => '/user/{id}/edit',
     *        'posts.index' => '/posts',
     *    ];
     *
     * 2. Generate URLs:
     *    route('home');                           // Returns '/'
     *    route('user.show', ['id' => 123]);       // Returns '/user/123'
     *    route('user.edit', ['id' => 123]);       // Returns '/user/123/edit'
     *
     * @param string $name       The name of the route.
     * @param array  $parameters An array of parameters to substitute in the route pattern.
     *
     * @return string The generated URL.
     * @throws InvalidArgumentException If the route name is not found.
     *
     * @example
     * <code>
     * // Define routes
     * $GLOBALS['routes'] = [
     *     'home' => '/',
     *     'user.show' => '/user/{id}',
     *     'user.edit' => '/user/{id}/edit',
     * ];
     *
     * echo route('home');                           // Returns '/'
     * echo route('user.show', ['id' => 123]);       // Returns '/user/123'
     * echo route('user.edit', ['id' => 123]);       // Returns '/user/123/edit'
     * </code>
     */
    function route(string $name, array $parameters = []): string
    {
        $routes = $GLOBALS['routes'] ?? [];
        
        if (!isset($routes[$name])) {
            throw new InvalidArgumentException("Route [{$name}] not found.");
        }
        
        $pattern = $routes[$name];
        
        // Replace {parameter} placeholders with actual values
        foreach ($parameters as $key => $value) {
            $pattern = str_replace('{' . $key . '}', urlencode((string)$value), $pattern);
        }

        if (preg_match('/\{[^}]+\}/', $pattern)) {
            throw new InvalidArgumentException("Missing route parameters for route [{$name}].");
        }
        
        return $pattern;
    }
}

if ( ! function_exists('auth')) {
    /**
     * Get the authenticated user.
     *
     * @return mixed
     *
     * @example
     * <code>
     * $user = auth(); // Gets the authenticated user
     * </code>
     */
    function auth(): mixed
    {
        return session('user');
    }
}

if ( ! function_exists('abort')) {
    /**
     * Abort the request with a specified status code and message.
     *
     * @param int    $code
     * @param string $message
     *
     * @return void
     *
     * @example
     * <code>
     * abort(404, 'Not Found'); // Aborts the request with a 404 status code and message
     * </code>
     */
    function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        exit();
    }
}

if ( ! function_exists('session')) {
    /**
     * Get or set session values.
     *
     * @param mixed|null $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    function session(mixed $key = null, mixed $default = null): mixed
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (is_null($key)) {
            return $_SESSION;
        }

        if (is_array($key)) {
            foreach ($key as $sessionKey => $sessionValue) {
                $_SESSION[$sessionKey] = $sessionValue;
            }

            return null;
        }

        return data_get($_SESSION, $key, $default);
    }
}

if ( ! function_exists('server')) {
    /**
     * Get a value from the $_SERVER superglobal.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $host = server('HTTP_HOST'); // Gets the value of HTTP_HOST from the $_SERVER superglobal
     * </code>
     */
    function server(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }
}

if ( ! function_exists('data_get')) {
    /**
     * Get a value from an array or object using dot notation.
     *
     * @param mixed        $target
     * @param array|string $key
     * @param mixed|null   $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $value = data_get($array, 'key'); // Gets the value of 'key' from the array
     * </code>
     */
    function data_get(mixed $target, array|string $key, mixed $default = null): mixed
    {
        $key = is_array($key) ? $key : explode('.', $key);

        if (empty($key)) {
            return $target;
        }

        foreach ($key as $segment) {
            if (is_array($target)) {
                if ( ! array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if ( ! $target->offsetExists($segment)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if ( ! property_exists($target, $segment) && ! isset($target->{$segment})) {
                    return value($default);
                }

                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if ( ! function_exists('value')) {
    /**
     * Return a value, resolving closures lazily.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @example
     * <code>
     * $default = value($someValue); // Returns the default value of $someValue
     * </code>
     */
    function value(mixed $value): mixed
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if ( ! function_exists('once')) {
    /**
     * Run a callback once per key and return the cached result.
     *
     * @param string   $key
     * @param callable $callback
     *
     * @return mixed
     */
    function once(string $key, callable $callback): mixed
    {
        static $results = [];

        if (array_key_exists($key, $results)) {
            return $results[$key];
        }

        return $results[$key] = $callback();
    }
}

if ( ! function_exists('retry')) {
    /**
     * Retry a callback until it succeeds or the attempt limit is reached.
     *
     * @param int      $times
     * @param callable $callback
     * @param int      $sleepMilliseconds
     *
     * @return mixed
     *
     * @throws Throwable
     */
    function retry(int $times, callable $callback, int $sleepMilliseconds = 0): mixed
    {
        if ($times < 1) {
            throw new InvalidArgumentException('Retry attempts must be at least 1.');
        }

        for ($attempt = 1; $attempt <= $times; $attempt++) {
            try {
                return $callback($attempt);
            } catch (Throwable $exception) {
                if ($attempt === $times) {
                    throw $exception;
                }

                if ($sleepMilliseconds > 0) {
                    usleep($sleepMilliseconds * 1000);
                }
            }
        }
    }
}

if ( ! function_exists('tap')) {
    /**
     * Pass a value to a callback, then return the original value.
     *
     * @param mixed    $value
     * @param callable $callback
     *
     * @return mixed
     */
    function tap(mixed $value, callable $callback): mixed
    {
        $callback($value);

        return $value;
    }
}

if ( ! function_exists('cache')) {
    /**
     * Simple cache helper function to store and retrieve data from a file-based cache.
     *
     * This function allows you to cache data using a key-value system and retrieve it later.
     * The cache is stored in files within a specified directory.
     *
     * Usage:
     * 1. Ensure the cache directory exists or can be created by the function.
     *    By default, the cache directory is 'cache' within the storage directory.
     *    The function will attempt to create this directory if it does not exist.
     *
     * 2. Store data in the cache:
     *    cache('my_key', 'my_value', 1800); // Caches 'my_value' under 'my_key' for 1800 seconds (30 minutes)
     *
     * 3. Retrieve data from the cache:
     *    $value = cache('my_key'); // Retrieves the cached value for 'my_key'
     *
     * 4. If the cached value has expired or does not exist, null is returned.
     *
     * Directory Permissions:
     * - The cache directory must be writable by the web server.
     * - The function attempts to create the directory with 0755 permissions if it does not exist.
     *
     * @param mixed|null $key     The cache key. Use null to return the cache directory path.
     * @param mixed|null $value   The value to cache. If null, the function will return the cached value.
     * @param int|null   $seconds The number of seconds to cache the value. Default is 3600 seconds (1 hour).
     *
     * @return mixed|null The cached value, or null if not found or expired.
     *
     * @example
     * <code>
     * // Caching a value
     * cache('my_key', 'my_value', 1800); // Cache 'my_value' for 1800 seconds (30 minutes)
     *
     * // Retrieving a cached value
     * $value = cache('my_key'); // Retrieve the cached value for 'my_key'
     *
     * // Retrieving the cache directory path
     * $cacheDirectory = cache(); // Returns the path to the cache directory
     * </code>
     */
    function cache(mixed $key = null, mixed $value = null, ?int $seconds = null): mixed
    {
        $cacheDir = config('cache_path') ?? storage('cache');

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (is_null($key)) {
            return $cacheDir;
        }

        $filePath = $cacheDir . '/' . md5($key) . '.cache';

        // Get mode: retrieve cached value
        if (is_null($value)) {
            $content = @file_get_contents($filePath);
            if ($content === false) {
                return null;
            }

            $cachedData = @json_decode($content, true);
            if (!$cachedData || !isset($cachedData['timestamp'], $cachedData['seconds'], $cachedData['value'])) {
                return null;
            }

            if ((time() - $cachedData['timestamp']) >= $cachedData['seconds']) {
                @unlink($filePath); // Clean up expired cache
                return null;
            }

            return $cachedData['value'];
        }

        // Set mode: store value in cache
        $seconds = $seconds ?? 3600;
        $data = json_encode(['value' => $value, 'timestamp' => time(), 'seconds' => $seconds]);
        file_put_contents($filePath, $data, LOCK_EX);

        return $value;
    }
}

if ( ! function_exists('data_set')) {
    /**
     * Set a value within an array or object using dot notation.
     *
     * @param mixed        $target
     * @param array|string $key
     * @param mixed        $value
     * @param bool         $overwrite
     *
     * @return mixed
     *
     * @example
     * <code>
     * data_set($array, 'key', 'value'); // Sets the value of 'key' in the array
     * </code>
     */
    function data_set(mixed &$target, array|string $key, mixed $value, bool $overwrite = true): mixed
    {
        $segments = is_array($key) ? $key : explode('.', $key);
        
        // Handle empty key
        if (empty($segments) || (count($segments) === 1 && $segments[0] === '')) {
            return $target;
        }

        // Store original reference for return
        $original = &$target;
        
        // Convert non-array/object to array at the start
        if (!is_array($target) && !is_object($target)) {
            $target = [];
        }

        foreach ($segments as $i => $segment) {
            unset($segments[$i]);

            if (is_array($target)) {
                if (count($segments)) {
                    if ( ! array_key_exists($segment, $target)) {
                        $target[$segment] = [];
                    }

                    $target = &$target[$segment];
                } else {
                    if ($overwrite || ! isset($target[$segment])) {
                        $target[$segment] = $value;
                    }
                }
            } elseif (is_object($target)) {
                if (count($segments)) {
                    if ( ! isset($target->{$segment})) {
                        $target->{$segment} = new stdClass();
                    }

                    $target = &$target->{$segment};
                } else {
                    if ($overwrite || ! isset($target->{$segment})) {
                        $target->{$segment} = $value;
                    }
                }
            }
        }

        return $original;
    }
}

if ( ! function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * This function encrypts a given value using AES-256-CBC encryption.
     * The encrypted value is base64 encoded for safe storage and transmission.
     * The encryption key is retrieved from the environment variables.
     *
     * @param mixed $value     The value to encrypt.
     * @param bool  $serialize Whether to serialize the value before encryption. Default is true.
     *
     * @return string The encrypted value, base64 encoded.
     *
     * @throws Exception If the encryption key is not set or encryption fails.
     * @example
     * <code>
     * $encrypted = encrypt('my_secret_value'); // Returns the encrypted string
     * </code>
     *
     * Usage:
     * 1. Ensure the APP_KEY is set in your environment variables (e.g., in the .env file).
     * 2. Use the encrypt() function to encrypt any value.
     *    Example: $encryptedValue = encrypt('my_secret_value');
     *
     */
    function encrypt(mixed $value, bool $serialize = true): string
    {
        $key = env('APP_KEY');
        if (empty($key)) {
            throw new Exception("Encryption key not set.");
        }

        // Handle base64 encoded keys (Laravel-style)
        if (strpos($key, 'base64:') === 0) {
            $key = base64_decode(substr($key, 7));
        }

        // Validate key length (32 bytes for AES-256)
        if (strlen($key) < 32) {
            throw new Exception("Encryption key must be at least 32 bytes.");
        }

        // Use HKDF for proper key derivation
        $salt = 'encryption-salt';
        $authKey = hash_hkdf('sha256', $key, 32, 'auth-key', $salt);
        $encKey = hash_hkdf('sha256', $key, 32, 'enc-key', $salt);

        $iv = random_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        $value = $serialize ? serialize($value) : $value;
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new Exception("Encryption failed.");
        }

        $mac = hash_hmac('sha256', $iv . $encrypted, $authKey, true);

        // Combine IV, MAC, and Ciphertext. Base64 encode the whole payload.
        return base64_encode($iv . $mac . $encrypted);
    }
}

if ( ! function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * This function decrypts a given encrypted value that was encrypted using AES-256-CBC.
     * The input value should be a base64 encoded string.
     * The encryption key is retrieved from the environment variables.
     *
     * @param string $value       The encrypted value to decrypt, base64 encoded.
     * @param bool   $unserialize Whether to unserialize the value after decryption. Default is true.
     *
     * @return mixed The decrypted value.
     *
     * @throws Exception If the encryption key is not set or decryption fails.
     * @example
     * <code>
     * $decrypted = decrypt($encryptedValue); // Returns the decrypted value
     * </code>
     *
     * Usage:
     * 1. Ensure the APP_KEY is set in your environment variables (e.g., in the .env file).
     * 2. Use the decrypt() function to decrypt any value that was encrypted with encrypt().
     *    Example: $decryptedValue = decrypt($encryptedValue);
     *
     */
    function decrypt(string $payload, bool $unserialize = true): mixed
    {
        $key = env('APP_KEY');
        if (empty($key)) {
            throw new Exception("Encryption key not set.");
        }

        // Handle base64 encoded keys (Laravel-style)
        if (strpos($key, 'base64:') === 0) {
            $key = base64_decode(substr($key, 7));
        }

        // Validate key length (32 bytes for AES-256)
        if (strlen($key) < 32) {
            throw new Exception("Encryption key must be at least 32 bytes.");
        }

        // Use HKDF for proper key derivation (must match encrypt function)
        $salt = 'encryption-salt';
        $authKey = hash_hkdf('sha256', $key, 32, 'auth-key', $salt);
        $encKey = hash_hkdf('sha256', $key, 32, 'enc-key', $salt);

        $decoded = base64_decode($payload);
        if ($decoded === false) {
            throw new Exception("Decryption failed: Invalid payload.");
        }

        $ivLength  = openssl_cipher_iv_length('AES-256-CBC');
        $macLength = 32; // SHA256 outputs 32 bytes

        if (strlen($decoded) < $ivLength + $macLength) {
            throw new Exception("Decryption failed: Invalid payload.");
        }

        $iv        = substr($decoded, 0, $ivLength);
        $storedMac = substr($decoded, $ivLength, $macLength);
        $encrypted = substr($decoded, $ivLength + $macLength);

        $calculatedMac = hash_hmac('sha256', $iv . $encrypted, $authKey, true);

        if ( ! hash_equals($storedMac, $calculatedMac)) {
            throw new Exception("Decryption failed: Invalid payload.");
        }

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new Exception("Decryption failed.");
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }
}

if ( ! function_exists('info')) {
    /**
     * Log an informational message.
     *
     * @param string $message The message to log.
     * @param array  $context An array of context information. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * info('User logged in', ['user_id' => 123]); // Logs the message with context
     * </code>
     */
    function info(string $message, array $context = []): void
    {
        logger('info', $message, $context);
    }
}

if ( ! function_exists('logger')) {
    /**
     * Log a message.
     *
     * This function logs a message to a file. Each log entry includes a timestamp, log level, message, and optional context information.
     * Logs are stored in files within a specified directory, with a new log file created for each day.
     *
     * Usage:
     * 1. Ensure the logs directory exists or can be created by the function.
     *    By default, the logs directory is '/logs' within the document root.
     *    The function will attempt to create this directory if it does not exist.
     *
     * 2. Log a message:
     *    logger('info', 'User logged in', ['user_id' => 123]); // Logs an info message with context
     *    logger('error', 'An error occurred'); // Logs an error message
     *
     * Directory Permissions:
     * - The logs directory must be writable by the web server.
     * - The function attempts to create the directory with 0777 permissions if it does not exist.
     *
     * Log File Naming:
     * - Log files are named based on the date they are created (e.g., '2024-06-01.log').
     * - A new log file is created each day.
     *
     * Log Entry Format:
     * - Each log entry includes a timestamp, log level, message, and optional context information in JSON format.
     *
     * @param string $level   The log level (e.g., 'info', 'error').
     * @param string $message The message to log.
     * @param array  $context An array of context information. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * // Log an informational message with context
     * logger('info', 'User logged in', ['user_id' => 123]);
     *
     * // Log an error message without context
     * logger('error', 'An error occurred');
     * </code>
     */
    function logger(string $level, string $message, array $context = []): void
    {
        $logDir = config('log_path') ?? storage('logs');

        if ( ! file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Define the log file path, creating a new log file each day
        $logFile = $logDir . '/' . date('Y-m-d') . '.log';

        // Create a timestamp for the log entry
        $timestamp = date('Y-m-d H:i:s');

        $logMessage = sprintf(
            "%s -- %s: %s %s",
            $timestamp,
            strtoupper($level),
            $message,
            empty($context) ? '' : json_encode($context)
        );

        $logMessage = trim($logMessage) . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

if ( ! function_exists('response')) {
    /**
     * Create an HTTP response.
     *
     * @param string $content The content of the response. Default is an empty string.
     * @param int    $status  The HTTP status code. Default is 200.
     * @param array  $headers An array of headers to include in the response. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * response('Hello, World!', 200, ['Content-Type' => 'text/plain']); // Creates a response
     * </code>
     */
    function response(string $content = '', int $status = 200, array $headers = []): void
    {
        http_response_code($status);

        foreach ($headers as $key => $value) {
            header("$key: $value");
        }

        echo $content;
    }
}

if ( ! function_exists('json_response')) {
    /**
     * Create a JSON HTTP response.
     *
     * @param mixed $data    The data to be encoded as JSON.
     * @param int   $status  The HTTP status code. Default is 200.
     * @param array $headers An array of headers to include in the response. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * json_response(['success' => true, 'message' => 'Data saved successfully'], 200); // Creates a JSON response
     * </code>
     */
    function json_response(mixed $data, int $status = 200, array $headers = []): void
    {
        $headers['Content-Type'] = 'application/json';
        response(json_encode($data), $status, $headers);
    }
}

if ( ! function_exists('storage')) {
    /**
     * Get the storage path.
     *
     * This function constructs and returns the full path to a storage directory or a specified file within the storage directory.
     * The storage directory path can be configured via a configuration setting, and if not set, defaults to a 'storage' directory next to the document root.
     *
     * Usage:
     * 1. Ensure the storage directory exists or can be created by the function.
     *    By default, the storage directory is '/storage' next to the document root.
     *    The function will attempt to create this directory if it does not exist.
     *
     * 2. Retrieve the storage path:
     *    $path = storage(); // Returns the path to the storage directory
     *    $filePath = storage('uploads/myfile.txt'); // Returns the full path to 'uploads/myfile.txt' within the storage directory
     *
     * Directory Permissions:
     * - The storage directory must be writable by the web server.
     * - The function attempts to create the directory with 0755 permissions if it does not exist.
     *
     * @param string $path The relative path to append to the storage directory. Default is an empty string.
     *
     * @return string The full path to the storage directory or the specified file.
     *
     * @throws Exception
     * @example
     * <code>
     * // Retrieve the storage directory path
     * $storagePath = storage(); // Returns the path to the storage directory
     *
     * // Retrieve the full path to a specific file within the storage directory
     * $filePath = storage('uploads/myfile.txt'); // Returns the full path to 'uploads/myfile.txt'
     * </code>
     */
    function storage(string $path = ''): string
    {
        $documentRoot = server('DOCUMENT_ROOT');
        $defaultStorageDir = $documentRoot ? dirname(rtrim($documentRoot, DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'storage' : sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'storage';
        $storageDir = config('storage_path') ?? $defaultStorageDir;

        if ( ! file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        // Construct the full path by combining the storage directory and the provided relative path
        return rtrim($storageDir, '/') . '/' . ltrim($path, '/');
    }
}

if ( ! function_exists('url')) {
    /**
     * Generate a URL.
     *
     * @param string|null $path       The path to append to the base URL. Default is null.
     * @param array       $parameters An array of query parameters to append to the URL. Default is an empty array.
     * @param bool|null   $secure     Whether to use HTTPS. Default is null, which will use the current scheme.
     *
     * @return string The generated URL.
     *
     * @example
     * <code>
     * echo url('user/profile'); // Returns the full URL to the user profile page
     * echo url('search', ['q' => 'laravel']); // Returns the URL with query parameters
     * echo url('user/profile', [], true); // Returns a secure HTTPS URL to the user profile page
     * </code>
     */
    function url(?string $path = null, array $parameters = [], ?bool $secure = null): string
    {
        $scheme  = ($secure ?? ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) ? 'https://' : 'http://';
        $host    = $_SERVER['HTTP_HOST'];
        $baseUrl = rtrim($scheme . $host, '/');

        $path = $path ?? '';
        $url = $path === '' ? $baseUrl : $baseUrl . '/' . ltrim($path, '/');

        if ( ! empty($parameters)) {
            $queryString = http_build_query($parameters);
            $url         .= '?' . $queryString;
        }

        return $url;
    }
}

if ( ! function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param string|null $value The string to escape. Defaults to null, resulting in an empty string.
     * @return string The escaped string.
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if ( ! function_exists('view')) {
    /**
     * Render a view template.
     *
     * This function renders a PHP view template, passing an array of data to it.
     * The view templates are expected to be PHP files located in a specified directory.
     *
     * Usage:
     * 1. Ensure the views directory exists within the document root.
     *    By default, the views directory is '/views' within the document root.
     *
     * 2. Create your view templates as PHP files in the views directory.
     *    Example: Create a file named 'home.php' in the '/views' directory.
     *
     * 3. Call the view() function to render a template with data.
     *    Example: view('home', ['name' => 'John']); // Renders the 'home' view with the provided data.
     *
     * Directory Structure:
     * - The views directory must be readable by the web server.
     * - View templates should be named with a '.php' extension.
     *
     * Data Passing:
     * - Data is extracted as individual variables. Access directly like `$name` or via `$viewData['name']`.
     * - Always use the `e()` helper function to escape output within the view, e.g., `<?= e($name) ?>`.
     * - The mergeData array allows additional data to be merged with the initial data array.
     *
     * @param string $view      The name of the view template (relative to the views directory).
     * @param array  $data      An array of data to pass to the view. Default is an empty array.
     * @param array  $mergeData An array of data to merge with the existing data. Default is an empty array.
     *
     * @return void
     * @throws RuntimeException If the view file is not found.
     *
     * @example
     * <code>
     * // Assuming a 'home.php' file exists in the '/views' directory
     * // home.php: <p>Hello, <?= e($name) ?>!</p>
     * view('home', ['name' => 'John']); // Renders the 'home' view with extracted variables
     * </code>
     */
    function view(string $view, array $data = [], array $mergeData = []): void
    {
        $viewDir = server('DOCUMENT_ROOT') . '/views';
        $basePath = realpath($viewDir);

        if ($basePath === false) {
            throw new RuntimeException("View directory not found at path [$viewDir].");
        }

        $candidatePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($view, DIRECTORY_SEPARATOR) . '.php';
        $viewPath = realpath($candidatePath);
        $basePrefix = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($viewPath === false || strncmp($viewPath, $basePrefix, strlen($basePrefix)) !== 0 || ! is_file($viewPath)) {
            throw new RuntimeException("View [$view] not found.");
        }

        // Merge the provided data arrays
        $data = array_merge($data, $mergeData);

        // Create an isolated scope for the view to prevent variable leakage
        $renderView = function($__viewPath, $__data) {
            // Extract variables for easier access in the view
            extract($__data, EXTR_SKIP);
            // Also make data available as $viewData for backward compatibility
            $viewData = $__data;
            include($__viewPath);
        };

        // Render the view in isolated scope
        $renderView($viewPath, $data);
    }
}

if ( ! function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param array             $array The array to pluck from.
     * @param array|string      $value The value to pluck.
     * @param array|string|null $key   The key to use for the plucked values.
     *
     * @return array The array of plucked values.
     *
     * @example
     * <code>
     * $array = [
     *     ['name' => 'John', 'age' => 30],
     *     ['name' => 'Jane', 'age' => 25],
     * ];
     * $names = array_pluck($array, 'name'); // Returns ['John', 'Jane']
     * $namesByAge = array_pluck($array, 'name', 'age'); // Returns [30 => 'John', 25 => 'Jane']
     * 
     * // Works with dot notation for nested values
     * $users = [
     *     ['user' => ['name' => 'John', 'id' => 1]],
     *     ['user' => ['name' => 'Jane', 'id' => 2]],
     * ];
     * $names = array_pluck($users, 'user.name'); // Returns ['John', 'Jane']
     * </code>
     */
    function array_pluck(array $array, array|string $value, array|string|null $key = null): array
    {
        $results = [];
        foreach ($array as $item) {
            $itemValue = data_get($item, $value);
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey           = data_get($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if ( ! function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param array        $array The array to modify.
     * @param array|string $keys  The keys to exclude from the array.
     *
     * @return array The modified array.
     *
     * @example
     * <code>
     * $array = ['name' => 'John', 'age' => 30, 'location' => 'NY'];
     * $result = array_except($array, ['age', 'location']); // Returns ['name' => 'John']
     * </code>
     */
    function array_except(array $array, array|string $keys): array
    {
        $keys = (array) $keys;
        return array_diff_key($array, array_flip($keys));
    }
}

if ( ! function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param array        $array The array to get items from.
     * @param array|string $keys  The keys to get from the array.
     *
     * @return array The subset of the array.
     *
     * @example
     * <code>
     * $array = ['name' => 'John', 'age' => 30, 'location' => 'NY'];
     * $result = array_only($array, ['name', 'location']); // Returns ['name' => 'John', 'location' => 'NY']
     * </code>
     */
    function array_only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }
}

if ( ! function_exists('lastline')) {
    /**
     * Get the last line of a log file.
     *
     * @param string $filePath The path to the log file.
     *
     * @return string The last line of the log file.
     */
    function lastline(string $filePath): string
    {
        if ( ! file_exists($filePath)) {
            return "Log file does not exist.";
        }

        if (filesize($filePath) === 0) {
            return '';
        }

        $lastLine = '';
        foreach (new SplFileObject($filePath, 'r') as $line) {
            if ($line !== false && $line !== '') {
                $lastLine = $line;
            }
        }

        return $lastLine;
    }
}
