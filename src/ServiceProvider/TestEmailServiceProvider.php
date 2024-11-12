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

namespace App\ServiceProvider;

use App\Command\SendTestEmailCommand;
use Cake\Core\ContainerInterface;
use Cake\Core\ServiceProvider;
use Cipherguard\SmtpSettings\Service\SmtpSettingsSendTestMailerService;
use Cipherguard\SmtpSettings\Service\SmtpSettingsTestEmailService;

class TestEmailServiceProvider extends ServiceProvider
{
    protected $provides = [
        SendTestEmailCommand::class,
        SmtpSettingsTestEmailService::class,
        SmtpSettingsSendTestMailerService::class,
    ];

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container
            ->add(SendTestEmailCommand::class)
            ->addArgument(SmtpSettingsTestEmailService::class);

        $container
            ->add(SmtpSettingsTestEmailService::class)
            ->addArgument(SmtpSettingsSendTestMailerService::class);

        $container->add(SmtpSettingsSendTestMailerService::class);
    }
}
