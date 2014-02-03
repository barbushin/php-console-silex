<?php

namespace PhpConsole\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;
use PhpConsole\Connector;
use PhpConsole\Helper;
use PhpConsole\Storage;
use PhpConsole\Handler;

/**
 * PHP Console Provider.
 */
class ServiceProvider implements ServiceProviderInterface {

	protected static $isInitialized;

	/** @var bool Is PHP Console server enabled */
	protected $isEnabled = true;
	/** @var string Path to PhpConsole classes directory */
	protected $phpConsolePathAlias = 'application.vendors.PhpConsole.src.PhpConsole';
	/** @var string Base path of all project sources to strip in errors source paths */
	protected $sourcesBasePath;
	/** @var string|null Server internal encoding */
	protected $serverEncoding;
	/** @var int|null Set headers size limit for your web-server. You can detect headers size limit by /PhpConsole/examples/utils/detect_headers_limit.php */
	protected $headersLimit;
	/** @var string|null Protect PHP Console connection by password */
	protected $password;
	/** @var bool Force connection by SSL for clients with PHP Console installed */
	protected $enableSslOnlyMode = false;
	/** @var array Set IP masks of clients that will be allowed to connect to PHP Console lie: array('192.168.*.*', '10.2.12*.*', '127.0.0.1') */
	protected $ipMasks = array();

	/** @var bool Enable errors handling */
	protected $handleErrors = true;
	/** @var bool Enable exceptions handling */
	protected $handleExceptions = true;

	/** @var int Maximum dumped vars array or object nested dump level */
	protected $dumperLevelLimit = 5;
	/** @var int Maximum dumped var same level array items or object properties number */
	protected $dumperItemsCountLimit = 100;
	/** @var int Maximum length of any string or dumped array item */
	protected $dumperItemSizeLimit = 50000;
	/** @var int Maximum approximate size of dumped vars result formatted in JSON */
	protected $dumperDumpSizeLimit = 500000;
	/** @var bool Convert callback items in dumper vars to (callback SomeClass::someMethod) strings */
	protected $dumperDetectCallbacks = true;
	/** @var bool Autodetect and append trace data to debug */
	protected $detectDumpTraceAndSource = false;

	/**
	 * @var bool Enable eval request to be handled by eval dispatcher. Must be called after all Connector configurations.
	 * $this->password is required to be set
	 * use $this->ipMasks & $this->enableSslOnlyMode for additional protection
	 */
	public $isEvalEnabled = false;

	/**
	 * Initializing errors handler as soon as possible
	 * @param Application $app
	 * @param Storage $storage Postponed response storage(except PhpConsole\Storage\Session)
	 * @param bool $handleErrors Enable errors handling
	 * @param bool $handleExceptions Enable exceptions handling
	 */
	public function __construct(Application $app, Storage $storage, $handleErrors = true, $handleExceptions = true) {
		$this->initPostponeStorage($storage);
		$this->initHandler($app, $handleErrors, $handleExceptions);
	}

	/**
	 * @return Connector
	 */
	public function getConnector() {
		return Connector::getInstance();
	}

	/**
	 * @return Handler
	 */
	public function getHandler() {
		return Handler::getInstance();
	}

	public function initHandler(Application $app, $handleErrors, $handleExceptions) {
		$handler = $this->getHandler();
		$handler->setHandleErrors($handleErrors);
		$handler->setHandleExceptions($handleExceptions);
		$handler->start();
		if($handleExceptions) {
			$app->error(function (\Exception $exception) use ($handler) {
				$handler->handleException($exception);
			});
		}
	}

	protected function initPostponeStorage(Storage $storage = null) {
		if($storage instanceof \PhpConsole\Storage\Session) {
			throw new \Exception('Unable to use PhpConsole\Storage\Session as PhpConsole storage interface because of problems with overridden $_SESSION handler in Silex');
		}
		Connector::setPostponeStorage($storage);
	}

	/**
	 * Registers services on the given app.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param Application $app An Application instance
	 */
	public function register(Application $app) {
		$connector = $this->getConnector();
		$handler = $this->getHandler();

		Helper::register($connector, $handler);

		$app['php-console.connector'] = $connector;
		$app['php-console.handler'] = $handler;
		$app->error(function (\Exception $exception) {
		});
	}

	public function boot(Application $app) {
		if($app['php-console.settings']) {
			foreach($app['php-console.settings'] as $option => $value) {
				$this->setOption($option, $value);
			}
		}
		if($this->isEnabled && $this->getConnector()->isActiveClient()) {
			$this->initConnectorSettings();
		}
		self::$isInitialized = true;
	}

	public function setOption($property, $value) {
		if(self::$isInitialized) {
			throw new \Exception('Unable to set option. Service provider already booted.');
		}
		if(!property_exists($this, $property)) {
			throw new \Exception('Unknown property "' . $property . '" in php-console settings list. See ' . __CLASS__ . ' properties for list of available settings');
		}
		$this->$property = $value;
	}

	/**
	 * @throws \Exception
	 */
	protected function initConnectorSettings() {
		$connector = $this->getConnector();

		if($this->sourcesBasePath) {
			$connector->setSourcesBasePath($this->sourcesBasePath);
		}
		if($this->serverEncoding) {
			$connector->setServerEncoding($this->serverEncoding);
		}
		if($this->password) {
			$connector->setPassword($this->password);
		}
		if($this->enableSslOnlyMode) {
			$connector->enableSslOnlyMode();
		}
		if($this->ipMasks) {
			$connector->setAllowedIpMasks($this->ipMasks);
		}
		if($this->headersLimit) {
			$connector->setHeadersLimit($this->headersLimit);
		}

		if($this->detectDumpTraceAndSource) {
			$connector->getDebugDispatcher()->detectTraceAndSource = true;
		}

		$dumper = $connector->getDumper();
		$dumper->levelLimit = $this->dumperLevelLimit;
		$dumper->itemsCountLimit = $this->dumperItemsCountLimit;
		$dumper->itemSizeLimit = $this->dumperItemSizeLimit;
		$dumper->dumpSizeLimit = $this->dumperDumpSizeLimit;
		$dumper->detectCallbacks = $this->dumperDetectCallbacks;

		if($this->isEvalEnabled) {
			$connector->startEvalRequestsListener();
		}
	}
}
