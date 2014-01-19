# PHP Console service provider

This is the [PHP Console](https://github.com/barbushin/php-console) service provider for [Silex](http://silex.sensiolabs.org).

Configuration
-------------

```php
// See PhpConsoleServiceProvider.php for supported options.

// Enable password protection (disabled by default).
$app['php_console.password'] = 'password';

// Enable remote PHP code execution.
$app['php_console.remote_php_execution'] = true;

// Enable PHP errors handler.
$app['php_console.track_errors'] = true;

// Register PHP Console
$app->register(new ChiTeck\SilexPhpConsole\PhpConsoleServiceProvider());
```

Usage
-------------

```php
// These three statements are equivalent.

$app['php_console']->getDebugDispatcher()->dispatchDebug($var, 'tags');

\PC::debug($var, 'tags');

$app->pc($var, 'tags'); // PhpConsoleTrait should be enabled.

```

Links
-------------

See https://github.com/barbushin/php-console for detail information about PHP Console.
