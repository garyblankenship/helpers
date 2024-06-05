<?php

if (!function_exists('request')) {
    /**
     * Get the value of a request variable from $_REQUEST.
     *
     * @param mixed $key
     * @param mixed $default
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
    function request($key, $default = null)
    {
        if (is_array($key)) {
            return array_map(fn($k) => request($k, $default), $key);
        }

        $key = filter_var($key, FILTER_SANITIZE_STRING);
        $value = $_REQUEST[$key] ?? $default;

        return is_array($value) ? filter_var_array($value, FILTER_SANITIZE_STRING) : filter_var($value, FILTER_SANITIZE_STRING);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the given value and die.
     *
     * @param mixed $data
     * @return void
     *
     * @example
     * <code>
     * dd($myVariable); // Dumps the variable and stops execution
     * </code>
     */
    function dd($data): void
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the given value.
     *
     * @param mixed $value The value to dump.
     *
     * @return void
     *
     * @example
     * <code>
     * dump($myVariable); // Outputs the contents of $myVariable in a readable format
     * </code>
     */
    function dump($value): void
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
    }
}

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $appEnv = env('APP_ENV', 'production'); // Gets the value of APP_ENV or 'production' if not set
     * </code>
     */
    function env(string $key, $default = null)
    {
        static $env = null;

        if ($env === null) {
            $env = [];
            $envFilePath = server('DOCUMENT_ROOT') . '/.env';
            if (file_exists($envFilePath)) {
                $envFile = fopen($envFilePath, 'r');
                while (($line = fgets($envFile)) !== false) {
                    $line = trim($line);
                    if ($line && $line[0] != '#') {
                        list($name, $value) = explode('=', $line, 2);
                        $env[$name] = $value;
                        $_ENV[$name] = $value;
                    }
                }
                fclose($envFile);
            }
        }

        return $env[$key] ?? $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get the value of a configuration setting.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $dbHost = config('database.host', 'localhost'); // Gets the value of database.host or 'localhost' if not set
     * </code>
     */
    function config(string $key, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $configFilePath = server('DOCUMENT_ROOT') . '/config.php';
            if (file_exists($configFilePath)) {
                $config = include $configFilePath;
            }
        }

        return data_get($config, $key, $default);
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a specified URL.
     *
     * @param string $url
     * @return void
     *
     * @example
     * <code>
     * redirect('https://example.com'); // Redirects to the specified URL
     * </code>
     */
    function redirect(string $url): void
    {
        header("Location: $url");
        exit();
    }
}

if (!function_exists('asset')) {
    /**
     * Generate a URL for an asset.
     *
     * @param string $path
     * @return string
     *
     * @example
     * <code>
     * $assetUrl = asset('images/logo.png'); // Generates the URL for the asset
     * </code>
     */
    function asset(string $path): string
    {
        return config('base_url') . '/' . trim($path, '/');
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve an old input value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     *
     * @example
     * <code>
     * $oldValue = old('username'); // Gets the old input value for 'username'
     * </code>
     */
    function old(string $key, $default = null)
    {
        return session('old.' . $key, $default);
    }
}

if (!function_exists('csrf_field')) {
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
        return '<input type="hidden" name="csrf_token" value="' . session('csrf_token') . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a hidden input field for the HTTP method.
     *
     * @param string $method
     * @return string
     *
     * @example
     * <code>
     * echo method_field('PUT'); // Outputs the hidden input field for the PUT method
     * </code>
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('route')) {
    /**
     * Handle routing for the request.
     *
     * @return void
     *
     * @example
     * <code>
     * route(); // Handles the routing for the request
     * </code>
     */
    function route(): void
    {
        $requestedMethod = $_SERVER['REQUEST_METHOD'];
        $requestedPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        [$dir, $method] = explode('/', $requestedPath) + [null, null];

        $functionToCall = strtolower($requestedMethod) . ucfirst($dir);

        if (function_exists($functionToCall)) {
            call_user_func($functionToCall, $method);
            return;
        }

        abort(404, "Not Found");
    }
}

if (!function_exists('auth')) {
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
    function auth()
    {
        return session('user');
    }
}

if (!function_exists('abort')) {
    /**
     * Abort the request with a specified status code and message.
     *
     * @param int $code
     * @param string $message
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
        echo $message;
        exit();
    }
}

if (!function_exists('session')) {
    /**
     * Get or set session values.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     *
     * @example
     * <code>
     * $value = session('key'); // Gets the session value for 'key'
     * session(['key' => 'value']); // Sets the session value for 'key'
     * </code>
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return $_SESSION;
        }

        if (is_array($key)) {
            foreach ($key as $sessionKey => $sessionValue) {
                $_SESSION[$sessionKey] = $sessionValue;
            }
            return null;
        }

        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('server')) {
    /**
     * Get a value from the $_SERVER superglobal.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     *
     * @example
     * <code>
     * $host = server('HTTP_HOST'); // Gets the value of HTTP_HOST from the $_SERVER superglobal
     * </code>
     */
    function server(string $key, $default = null)
    {
        return $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('data_get')) {
    /**
     * Get a value from an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     *
     * @example
     * <code>
     * $value = data_get($array, 'key'); // Gets the value of 'key' from the array
     * </code>
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $segment) {
            if (is_array($target)) {
                if (!array_key_exists($segment, $target)) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif ($target instanceof ArrayAccess) {
                if (!isset($target[$segment])) {
                    return value($default);
                }

                $target = $target[$segment];
            } elseif (is_object($target)) {
                if (!isset($target->{$segment})) {
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

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     *
     * @example
     * <code>
     * $default = value($someValue); // Returns the default value of $someValue
     * </code>
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('cache')) {
    /**
     * Simple cache helper function to store and retrieve data from a file-based cache.
     *
     * @param string|null $key The cache key.
     * @param mixed|null $value The value to cache. If null, the function will return the cached value.
     * @param int $seconds The number of seconds to cache the value. Default is 3600 seconds (1 hour).
     *
     * @return mixed|null The cached value, or null if not found or expired.
     *
     * @example
     * <code>
     * cache('my_key', 'my_value', 1800); // Cache 'my_value' for 1800 seconds (30 minutes)
     * $value = cache('my_key'); // Retrieve the cached value
     * </code>
     */
    function cache($key = null, $value = null, int $seconds = 3600)
    {
        $cacheDir = server('DOCUMENT_ROOT') . '/cache';

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        if (is_null($key)) {
            return $cacheDir;
        }

        $filePath = $cacheDir . '/' . md5($key) . '.cache';

        if (is_null($value)) {
            if (file_exists($filePath) && (filemtime($filePath) + $seconds) > time()) {
                return unserialize(file_get_contents($filePath));
            }

            return null;
        }

        file_put_contents($filePath, serialize($value));

        return $value;
    }
}

if (!function_exists('data_set')) {
    /**
     * Set a value within an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     *
     * @example
     * <code>
     * data_set($array, 'key', 'value'); // Sets the value of 'key' in the array
     * </code>
     */
    function data_set(&$target, $key, $value, bool $overwrite = true)
    {
        if (is_null($key)) {
            return $target = $value;
        }

        $segments = is_array($key) ? $key : explode('.', $key);

        foreach ($segments as $i => $segment) {
            unset($segments[$i]);

            if (is_array($target)) {
                if (count($segments)) {
                    if (!array_key_exists($segment, $target)) {
                        $target[$segment] = [];
                    }

                    $target = &$target[$segment];
                } else {
                    if ($overwrite || !isset($target[$segment])) {
                        $target[$segment] = $value;
                    }
                }
            } elseif (is_object($target)) {
                if (count($segments)) {
                    if (!isset($target->{$segment})) {
                        $target->{$segment} = [];
                    }

                    $target = &$target->{$segment};
                } else {
                    if ($overwrite || !isset($target->{$segment})) {
                        $target->{$segment} = $value;
                    }
                }
            } else {
                $target = [];
            }
        }

        return $target;
    }
}

if (!function_exists('encrypt')) {
    /**
     * Encrypt the given value.
     *
     * @param mixed $value The value to encrypt.
     * @param bool $serialize Whether to serialize the value before encryption. Default is true.
     *
     * @return string The encrypted value.
     *
     * @example
     * <code>
     * $encrypted = encrypt('my_secret_value'); // Returns the encrypted string
     * </code>
     */
    function encrypt($value, bool $serialize = true): string
    {
        $key = env('app_key');
        $iv = random_bytes(16);

        $value = $serialize ? serialize($value) : $value;
        $encrypted = openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('decrypt')) {
    /**
     * Decrypt the given value.
     *
     * @param string $value The value to decrypt.
     * @param bool $unserialize Whether to unserialize the value after decryption. Default is true.
     *
     * @return mixed The decrypted value.
     *
     * @example
     * <code>
     * $decrypted = decrypt($encryptedValue); // Returns the decrypted value
     * </code>
     */
    function decrypt(string $value, bool $unserialize = true)
    {
        $key = env('app_key');
        $value = base64_decode($value);
        $iv = substr($value, 0, 16);
        $encrypted = substr($value, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }
}

if (!function_exists('info')) {
    /**
     * Log an informational message.
     *
     * @param string $message The message to log.
     * @param array $context An array of context information. Default is an empty array.
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

if (!function_exists('logger')) {
    /**
     * Log a message.
     *
     * @param string $level The log level (e.g., 'info', 'error').
     * @param string $message The message to log.
     * @param array $context An array of context information. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * logger('info', 'User logged in', ['user_id' => 123]); // Logs the message with context
     * </code>
     */
    function logger(string $level, string $message, array $context = []): void
    {
        $logDir = server('DOCUMENT_ROOT') . '/logs';

        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');

        $contextString = json_encode($context);
        $logMessage = "[$timestamp] $level: $message $contextString" . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

if (!function_exists('response')) {
    /**
     * Create an HTTP response.
     *
     * @param string $content The content of the response. Default is an empty string.
     * @param int $status The HTTP status code. Default is 200.
     * @param array $headers An array of headers to include in the response. Default is an empty array.
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

if (!function_exists('json_response')) {
    /**
     * Create a JSON HTTP response.
     *
     * @param mixed $data The data to be encoded as JSON.
     * @param int $status The HTTP status code. Default is 200.
     * @param array $headers An array of headers to include in the response. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * json_response(['success' => true, 'message' => 'Data saved successfully'], 200); // Creates a JSON response
     * </code>
     */
    function json_response($data, int $status = 200, array $headers = []): void
    {
        $headers['Content-Type'] = 'application/json';
        response(json_encode($data), $status, $headers);
    }
}

if (!function_exists('storage')) {
    /**
     * Get the storage path.
     *
     * @param string $path The relative path to append to the storage directory. Default is an empty string.
     *
     * @return string The full path to the storage directory or the specified file.
     *
     * @example
     * <code>
     * $path = storage('uploads/myfile.txt'); // Returns the full path to the specified file in the storage directory
     * </code>
     */
    function storage(string $path = ''): string
    {
        $storageDir = config('storage_path') ?? server('DOCUMENT_ROOT') . '/storage';

        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0777, true);
        }

        return rtrim($storageDir, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL.
     *
     * @param string|null $path The path to append to the base URL. Default is null.
     * @param array $parameters An array of query parameters to append to the URL. Default is an empty array.
     * @param bool|null $secure Whether to use HTTPS. Default is null, which will use the current scheme.
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
    function url(string $path = null, array $parameters = [], ?bool $secure = null): string
    {
        $scheme = ($secure ?? (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = rtrim($scheme . $host, '/');

        $url = $baseUrl . '/' . ltrim($path, '/');

        if (!empty($parameters)) {
            $queryString = http_build_query($parameters);
            $url .= '?' . $queryString;
        }

        return $url;
    }
}

if (!function_exists('view')) {
    /**
     * Render a view template.
     *
     * @param string $view The name of the view template.
     * @param array $data An array of data to pass to the view. Default is an empty array.
     * @param array $mergeData An array of data to merge with the existing data. Default is an empty array.
     *
     * @return void
     *
     * @example
     * <code>
     * view('home', ['name' => 'John']); // Renders the 'home' view with the provided data
     * </code>
     */
    function view(string $view, array $data = [], array $mergeData = []): void
    {
        $viewDir = server('DOCUMENT_ROOT') . '/views';
        $viewPath = rtrim($viewDir, '/') . '/' . ltrim($view, '/') . '.php';

        if (!file_exists($viewPath)) {
            abort(404, "View [$view] not found.");
        }

        $data = array_merge($data, $mergeData);

        extract($data);
        include($viewPath);
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array.
     *
     * @param array $array The array to pluck from.
     * @param string|array $value The value to pluck.
     * @param string|array|null $key The key to use for the plucked values.
     *
     * @return array The array of plucked values.
     *
     * @example
     * <code>
     * $array = [
     * ['name' => 'John', 'age' => 30],
     * ['name' => 'Jane', 'age' => 25],
     * ];
     * $names = array_pluck($array, 'name'); // Returns ['John', 'Jane']
     * </code>
     */
    function array_pluck(array $array, $value, $key = null): array
    {
        $results = [];
        foreach ($array as $item) {
            $itemValue = data_get($item, $value);
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param array $array The array to modify.
     * @param array|string $keys The keys to exclude from the array.
     *
     * @return array The modified array.
     *
     * @example
     * <code>
     * $array = ['name' => 'John', 'age' => 30, 'location' => 'NY'];
     * $result = array_except($array, ['age', 'location']); // Returns ['name' => 'John']
     * </code>
     */
    function array_except(array $array, $keys): array
    {
        foreach ((array)$keys as $key) {
            unset($array[$key]);
        }

        return $array;
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array.
     *
     * @param array $array The array to get items from.
     * @param array|string $keys The keys to get from the array.
     *
     * @return array The subset of the array.
     *
     * @example
     * <code>
     * $array = ['name' => 'John', 'age' => 30, 'location' => 'NY'];
     * $result = array_only($array, ['name', 'location']); // Returns ['name' => 'John', 'location' => 'NY']
     * </code>
     */
    function array_only(array $array, $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }
}