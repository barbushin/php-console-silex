<?php


namespace ChiTeck\SilexPhpConsole;


/**
 * PHP Console trait.
 */
trait PhpConsoleTrait
{
    /**
     * Wrapper for dispatchDebug()
     *
     * Send debug data message to client
     * @param mixed $data
     * @param string $tags Tags separated by dot, e.g. "low.db.billing"
     *
     * @see PhpConsole\Dispatcher\Debug
     */
    public function pc($data, $tags = null)
    {
        $this['php_console']->getDebugDispatcher()->dispatchDebug($data, $tags);
    }
}
