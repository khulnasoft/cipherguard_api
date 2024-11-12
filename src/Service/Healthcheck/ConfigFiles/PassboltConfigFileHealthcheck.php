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

namespace App\Service\Healthcheck\ConfigFiles;

use App\Service\Healthcheck\HealthcheckCliInterface;
use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Service\Healthcheck\HealthcheckServiceInterface;

class CipherguardConfigFileHealthcheck implements HealthcheckServiceInterface, HealthcheckCliInterface
{
    /**
     * Status of this health check if it is passed or failed.
     *
     * @var bool
     */
    private bool $status = false;

    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        $this->status = (file_exists(CONFIG . 'cipherguard.php'));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return HealthcheckServiceCollector::DOMAIN_CONFIG_FILES;
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
        return 'warning';
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The cipherguard config file is present');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('The cipherguard config file is missing in {0}', CONFIG);
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        return [
            __('Copy {0} to {1}', CONFIG . 'cipherguard.default.php', CONFIG . 'cipherguard.php'),
            __('The cipherguard config file is not required if cipherguard is configured with environment variables'),
        ];
    }

    /**
     * CLI Option for this check.
     *
     * @return string
     */
    public function cliOption(): string
    {
        return HealthcheckServiceCollector::DOMAIN_CONFIG_FILES;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'cipherguard';
    }
}
