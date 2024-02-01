<?php

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         3.8.0
 */
use Cake\Routing\RouteBuilder;
use Cipherguard\SmtpSettings\Middleware\SmtpSettingsSecurityMiddleware;

/** @var \Cake\Routing\RouteBuilder $routes */

$routes->plugin('Cipherguard/SmtpSettings', ['path' => '/smtp'], function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);
    $routes->registerMiddleware(SmtpSettingsSecurityMiddleware::class, new SmtpSettingsSecurityMiddleware());

    $routes->connect('/settings', ['controller' => 'SmtpSettingsGet', 'action' => 'get'])
        ->setMethods(['GET'])
        ->setMiddleware([SmtpSettingsSecurityMiddleware::class]);

    $routes->connect('/settings', ['controller' => 'SmtpSettingsPost', 'action' => 'post'])
        ->setMethods(['POST', 'PUT'])
        ->setMiddleware([SmtpSettingsSecurityMiddleware::class]);

    $routes->connect('/email', ['controller' => 'SmtpSettingsEmail', 'action' => 'sendTestEmail'])
        ->setMethods(['POST'])
        ->setMiddleware([SmtpSettingsSecurityMiddleware::class]);
});
