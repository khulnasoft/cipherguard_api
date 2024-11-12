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
 * @since         4.7.0
 */

namespace App\Service\Healthcheck\Application;

use App\Service\Healthcheck\HealthcheckCliInterface;
use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Service\Healthcheck\HealthcheckServiceInterface;
use Cipherguard\SelfRegistration\Service\Healthcheck\SelfRegistrationHealthcheckService;

class SelfRegistrationPluginEnabledApplicationHealthcheck implements HealthcheckServiceInterface, HealthcheckCliInterface // phpcs:ignore
{
    /**
     * Status of this health check if it is passed or failed.
     *
     * @var bool
     */
    private bool $status = false;

    /**
     * @var \Cipherguard\SelfRegistration\Service\Healthcheck\SelfRegistrationHealthcheckService
     */
    private SelfRegistrationHealthcheckService $selfRegistrationHealthcheckService;

    /**
     * @param \Cipherguard\SelfRegistration\Service\Healthcheck\SelfRegistrationHealthcheckService $selfRegistrationHealthcheckService Self registration health check service.
     */
    public function __construct(SelfRegistrationHealthcheckService $selfRegistrationHealthcheckService)
    {
        $this->selfRegistrationHealthcheckService = $selfRegistrationHealthcheckService;
    }

    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        $selfRegistrationHealthcheck = $this->selfRegistrationHealthcheckService->getHealthcheck();
        $this->status = $selfRegistrationHealthcheck['isSelfRegistrationPluginEnabled'];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return HealthcheckServiceCollector::DOMAIN_APPLICATION;
    }

    /**
     * @inheritDoc
     */
    public function isPassed(): bool
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function level(): string
    {
        return HealthcheckServiceCollector::LEVEL_NOTICE;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The Self Registration plugin is enabled.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('The Self Registration plugin is disabled.');
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        return __('Enable the plugin in order to define self registration settings.');
    }

    /**
     * CLI Option for this check.
     *
     * @return string
     */
    public function cliOption(): string
    {
        return HealthcheckServiceCollector::DOMAIN_APPLICATION;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'registrationClosed.isSelfRegistrationPluginEnabled';
    }
}
