<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.github.io)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.github.io)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.github.io Cipherguard(tm)
 * @since         3.8.0
 */
namespace Cipherguard\SmtpSettings;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cipherguard\SmtpSettings\Event\SmtpTransportBeforeSendEventListener;
use Cipherguard\SmtpSettings\Service\Healthcheck\CustomSslOptionsSmtpSettingsHealthcheck;
use Cipherguard\SmtpSettings\Service\Healthcheck\SettingsValidationSmtpSettingsHealthcheck;
use Cipherguard\SmtpSettings\Service\Healthcheck\SmtpSettingsEndpointsDisabledHealthcheck;
use Cipherguard\SmtpSettings\Service\Healthcheck\SmtpSettingsSettingsSourceHealthcheck;

class SmtpSettingsPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);

        // Before sending an email, apply the SMTP settings found in DB (or fallback on file).
        $app->getEventManager()->on(new SmtpTransportBeforeSendEventListener());
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container->add(SettingsValidationSmtpSettingsHealthcheck::class);
        $container->add(SmtpSettingsSettingsSourceHealthcheck::class);
        $container->add(SmtpSettingsEndpointsDisabledHealthcheck::class);
        $container->add(CustomSslOptionsSmtpSettingsHealthcheck::class);
        $container
            ->extend(HealthcheckServiceCollector::class)
            ->addMethodCall('addService', [SettingsValidationSmtpSettingsHealthcheck::class])
            ->addMethodCall('addService', [SmtpSettingsSettingsSourceHealthcheck::class])
            ->addMethodCall('addService', [SmtpSettingsEndpointsDisabledHealthcheck::class])
            ->addMethodCall('addService', [CustomSslOptionsSmtpSettingsHealthcheck::class]);
    }
}
