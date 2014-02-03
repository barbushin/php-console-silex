# PHP Console service provider

PHP Console allows you to handle PHP errors & exceptions, dump variables, execute PHP code remotely and many other things using [Google Chrome extension PHP Console](https://chrome.google.com/webstore/detail/php-console/nfhmhhlpfleoednkpnnnkolmclajemef) and [PhpConsole server library](https://github.com/barbushin/php-console).

This packages integrates [PHP Console server library](https://github.com/barbushin/php-console) with [Silex](http://silex.sensiolabs.org) as configurable service provider.

## Installation

Require this package in Silex project `composer.json` and run `composer update`

    "php-console/silex-service-provider": "1.*"

## Configuration

To handle errors occurred on Silex initialization PhpConsole service provider should be initialized right after `Silex\Application`:

	$app = new Silex\Application();

	// All settings are optional, so you can remove any key in this array
	$app['php-console.settings'] = array(
	  'sourcesBasePath' => dirname(__DIR__),
	  'serverEncoding' => null,
	  'headersLimit' => null,
	  'password' => null,
	  'enableSslOnlyMode' => false,
	  'ipMasks' => array(),
	  'isEvalEnabled' => false,
	  'dumperLevelLimit' => 5,
	  'dumperItemsCountLimit' => 100,
	  'dumperItemSizeLimit' => 5000,
	  'dumperDumpSizeLimit' => 500000,
	  'dumperDetectCallbacks' => true,
	  'detectDumpTraceAndSource' => false,
	);

	$app->register(new PhpConsole\Silex\ServiceProvider($app,
  	new \PhpConsole\Storage\File(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php-console.data') // any writable path
	));

See [PhpConsole\Silex\ServiceProvider](/src/PhpConsole/Silex/ServiceProvider.php) for detailed settings description.

## Usage

When PhpConsole service provider is registered all errors and exceptions will be handled automatically.

Now you can debug vars using PhpConsole global helper class `PC`:

	PC::debug($var, 'tags');

Also you can extended `Silex\Application` class with `use PhpConsole\Silex\ApplicationHelperTrait` and debug using:

$app->pc($var, 'tags');
