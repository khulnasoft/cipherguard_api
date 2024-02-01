<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.8
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
$isCli = PHP_SAPI === 'cli';

/*
 * Configure paths required to find CakePHP + general filepath constants
 */
require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';

/*
 * Bootstrap CakePHP.
 *
 * Does the various bits of setup that CakePHP needs to do.
 * This includes:
 *
 * - Registering the CakePHP autoloader.
 * - Setting the default application paths.
 */
require CORE_PATH . 'config' . DS . 'bootstrap.php';

use App\Mailer\Transport\DebugTransport;
use App\Mailer\Transport\SmtpTransport;
use Cake\Cache\Cache;
use Cake\Database\Type\JsonType;
use Cake\Database\TypeFactory;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Database\Type\StringType;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorTrap;
use Cake\Error\ExceptionTrap;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\Routing\Router;
use Cake\Utility\Security;

/*
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
    Configure::load('default', 'default', false); // cipherguard default config
    Configure::load('audit_logs', 'default', true); // audit logs config
    if (\file_exists(CONFIG . DS . 'cipherguard.php')) {
        Configure::load('cipherguard', 'default', true); // merge with default config

         // Deduplicate multiple from address for email
         // Can happen if from is also set as array in cipherguard.php
        $from = Configure::read('Email.default.from');
        if (isset($from) && is_array($from) && count($from) > 1) {
            Configure::write('Email.default.from', array_slice($from, -1, count($from))); // pick the last one
        }
    }
    Configure::load('version', 'default', true);
} catch (\Exception $e) {
    // let cli handle issues
    if (!$isCli) {
        exit($e->getMessage() . "\n");
    }
}

/**
 * Overwrite these paths. This is a helper to ensure CakePHP3 to 4 retro-compatibility
 * It will also be helpful if we ever have multiple plugin directories. Same goes for locales.
 */
Configure::write('App.paths', [
    'plugins' => [ROOT . DS . 'plugins' . DS],
    'templates' => [ROOT . DS . 'templates' . DS],
    'locales' => [RESOURCES . 'locales' . DS],
]);

/*
 * Load an environment local configuration file to provide overrides to your configuration.
 * Notice: For security reasons app_local.php **should not** be included in your git repo.
 */
//Configure::load('app_local', 'default');

/*
 * When debug = true the metadata cache should only last
 * for a short time.
 */
if (Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+2 minutes');
    Configure::write('Cache._cake_core_.duration', '+2 minutes');
    Configure::write('Cache._cake_routes_.duration', '+2 seconds');
}

/*
 * Set the default server timezone. Using UTC makes time calculations / conversions easier.
 * Check https://php.net/manual/en/timezones.php for list of valid timezone strings.
 */
date_default_timezone_set(Configure::read('App.defaultTimezone', 'UTC'));

/*
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding(Configure::read('App.encoding'));

/*
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', Configure::read('App.defaultLocale', 'en_UK'));

if (!Configure::read('debug')) {
    Configure::write('Error.errorLevel', E_ALL ^ E_DEPRECATED ^ E_USER_DEPRECATED);
} else {
    /**
     * Enable traces in logs when debug mode is enabled.
     */
    Configure::write('Error.log', true);
    Configure::write('Error.trace', true);
}
/*
 * Register application error and exception handlers.
 */
(new ErrorTrap(Configure::read('Error')))->register();
(new ExceptionTrap(Configure::read('Error')))->register();

/*
 * Include the CLI bootstrap overrides.
 */
if ($isCli) {
    require CONFIG . 'bootstrap_cli.php';
}

/*
 * Set the full base URL.
 * This URL is used as the base of all absolute links.
 */
$fullBaseUrl = Configure::read('App.fullBaseUrl');
if (!$fullBaseUrl) {
    /*
     * When using proxies or load balancers, SSL/TLS connections might
     * get terminated before reaching the server. If you trust the proxy,
     * you can enable `$trustProxy` to rely on the `X-Forwarded-Proto`
     * header to determine whether to generate URLs using `https`.
     *
     * See also https://book.cakephp.org/4/en/controllers/request-response.html#trusting-proxy-headers
     */
    $trustProxy = false;

    $s = null;
    if (env('HTTPS') || ($trustProxy && env('HTTP_X_FORWARDED_PROTO') === 'https')) {
        $s = 's';
    }

    $httpHost = env('HTTP_HOST');
    if (isset($httpHost)) {
        $fullBaseUrl = 'http' . $s . '://' . $httpHost;
    }
    unset($httpHost, $s);
}
if ($fullBaseUrl) {
    Router::fullBaseUrl($fullBaseUrl);
}
unset($fullBaseUrl);

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));
Configure::write('EmailTransport.default.className', SmtpTransport::class);
Configure::write('EmailTransport.Debug.className', DebugTransport::class);
TransportFactory::setConfig(Configure::consume('EmailTransport'));
Mailer::setConfig(Configure::consume('Email'));
Log::setConfig(Configure::consume('Log'));
Security::setSalt(Configure::consume('Security.salt'));

// Enforce the json time format
FrozenTime::setJsonEncodeFormat("yyyy-MM-dd'T'HH':'mm':'ssxxx");

/*
 * Setup detectors for mobile and tablet.
 */
//ServerRequest::addDetector('mobile', function ($request) {
//    $detector = new \Detection\MobileDetect();
//    return $detector->isMobile();
//});
//ServerRequest::addDetector('tablet', function ($request) {
//    $detector = new \Detection\MobileDetect();
//    return $detector->isTablet();
//});

/**
 * Add custom Json type to be used for any database field.
 *
 * This is helpful because we are storing json value inside database column. This class handles converting array to json
 * and vice versa, so we can directly set array value to particular field, and it will handle converting the value to
 * valid type for us.
 *
 * @see https://book.cakephp.org/4/en/orm/database-basics.html#adding-custom-types
 */
TypeFactory::map('json', JsonType::class);
// There is no time-specific type in Cake
TypeFactory::map('time', StringType::class);

/*
 * Set process user constant
 */
$uid = posix_getuid();
$user = posix_getpwuid($uid);
define('PROCESS_USER', $user['name']);

// Are we running cipherguard pro?
define('CIPHERGUARD_PRO', Configure::read('cipherguard.edition') === 'pro');

/**
 * Set email queue plugin serialization type to JSON.
 */
Configure::write('EmailQueue.serialization_type', 'email_queue.json');
