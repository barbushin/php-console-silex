<?php namespace PhpConsole\Silex;

/**
 * PHP Console trait to be used in Silex\Application class
 */
trait ApplicationHelperTrait {

	/**
	 * Wrapper for dispatchDebug()
	 *
	 * Send debug data message to client
	 * @param mixed $data
	 * @param string $tags Tags separated by dot, e.g. "low.db.billing"
	 *
	 * @see PhpConsole\Dispatcher\Debug
	 */
	public function pc($data, $tags = null) {
		if(isset($this['php-console.connector'])) {
			$this['php-console.connector']->getDebugDispatcher()->dispatchDebug($data, $tags, 1);
		}
	}
}
