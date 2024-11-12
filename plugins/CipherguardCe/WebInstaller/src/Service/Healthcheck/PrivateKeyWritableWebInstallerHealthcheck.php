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

namespace Cipherguard\WebInstaller\Service\Healthcheck;

use App\Service\Healthcheck\Gpg\AbstractGpgHealthcheck;
use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Service\Healthcheck\HealthcheckServiceInterface;

class PrivateKeyWritableWebInstallerHealthcheck extends AbstractGpgHealthcheck
{
    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        $keyFolderWritable = is_writable(dirname($this->getPrivateServerKey()));
        $privateKeyPath = $this->getPrivateServerKey();
        $this->status = file_exists($privateKeyPath) ? is_writable($privateKeyPath) : $keyFolderWritable;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return HealthcheckServiceCollector::DOMAIN_ENVIRONMENT;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The server OpenPGP private key file is writable.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('The server OpenPGP private key file is not writable.');
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        $privateKeyPath = $this->getPrivateServerKey();

        return [
            __('Ensure the file {0} is writable by the webserver user.', CONFIG . 'gpg' . DS . $privateKeyPath),
            __('you can try:'),
            'sudo chown ' . PROCESS_USER . ':' . PROCESS_USER . ' ' . CONFIG . 'gpg',
            'sudo chmod 775 $(find ' . CONFIG . 'gpg -type d)',
        ];
    }

    /**
     * CLI Option for this check.
     *
     * @return string
     */
    public function cliOption(): string
    {
        return HealthcheckServiceCollector::DOMAIN_ENVIRONMENT;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'privateKeyWritable';
    }
}
