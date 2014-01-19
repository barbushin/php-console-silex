<?php

namespace ChiTeck\SilexPhpConsole;

use Silex\ServiceProviderInterface;
use Silex\Application;
use PhpConsole\Connector;
use PhpConsole\Storage;
use PhpConsole\Helper;
use PhpConsole\Handler;


/**
 * PHP Console Provider.
 */
class PhpConsoleServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {

        $app['php_console'] = $app->share(
            function () {
                return Connector::getInstance();
            }
        );

    }

    public function boot(Application $app)
    {

        // We should initialize PHP console as soon as possible.
        if (empty($app['php_console.data_file'])) {
            $app['php_console.data_file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pc.data';
        }
        $storage = new Storage\File($app['php_console.data_file']);
        Connector::setPostponeStorage($storage);

        Helper::register();
        $connector = Connector::getInstance();

        if (isset($app['php_console.password'])) {
            $connector->setPassword($app['php_console.password'], true);
        }

        if (isset($app['php_console.password'], $app['php_console.remote_php_execution'])) {
            $eval_provider = $connector->getEvalDispatcher()->getEvalProvider();
            $eval_provider->setOpenBaseDirs(array(__DIR__));
            $connector->startEvalRequestsListener();
        }

        if (isset($app['php_console.source_base_path'])) {
            $connector->setSourcesBasePath($app['php_console.source_base_path']);
        }

        if (isset($app['php_console.server_encoding'])) {
            $connector->setServerEncoding($app['php_console.server_encoding']);
        }

        if (!empty($app['php_console.ssl_only_mode'])) {
            $connector->enableSslOnlyMode();
        }

        if (!empty($app['php_console.allowed_ip_masks'])) {
            $connector->setAllowedIpMasks($app['php_console.allowed_ip_masks']);
        }

        if (!empty($app['php_console.track_errors'])) {
            Handler::getInstance()->start();
        }

    }

}
