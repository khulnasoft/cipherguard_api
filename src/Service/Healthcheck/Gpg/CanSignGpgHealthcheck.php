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

namespace App\Service\Healthcheck\Gpg;

use App\Service\Healthcheck\HealthcheckServiceInterface;
use App\Service\Healthcheck\SkipHealthcheckInterface;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Cake\Core\Exception\CakeException;

class CanSignGpgHealthcheck extends AbstractGpgHealthcheck implements SkipHealthcheckInterface
{
    private PublicKeyInKeyringGpgHealthcheck $publicKeyInKeyringGpgHealthcheck;

    private bool $isSkipped = false;

    /**
     * @param \App\Service\Healthcheck\Gpg\PublicKeyInKeyringGpgHealthcheck $publicKeyInKeyringGpgHealthcheck service
     */
    public function __construct(PublicKeyInKeyringGpgHealthcheck $publicKeyInKeyringGpgHealthcheck)
    {
        $this->publicKeyInKeyringGpgHealthcheck = $publicKeyInKeyringGpgHealthcheck;
    }

    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        if (!$this->publicKeyInKeyringGpgHealthcheck->isPassed()) {
            $this->markAsSkipped();

            return $this;
        }

        $gpg = OpenPGPBackendFactory::get();
        $gpg->setSignKeyFromFingerprint(
            $this->getServerKeyFingerprint(),
            $this->getServerKeyPassphrase()
        );
        try {
            $gpg->sign('test message');
            $this->status = true;
        } catch (CakeException $e) {
            // Do nothing
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The private key can be used to sign a message.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('The private key cannot be used to sign a message');
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        return [
            __('Make sure that the server private key is valid and that there is no passphrase.'),
            __('Make sure you imported the private server key in the keyring of the webserver user.'),
            __('you can try:'),
            'sudo su -s /bin/bash -c "gpg --home ' . $this->getGpgHome() . ' --import ' . $this->getPrivateServerKey() . '" ' . PROCESS_USER, // phpcs:ignore
        ];
    }

    /**
     * @inheritDoc
     */
    public function markAsSkipped(): void
    {
        $this->isSkipped = true;
    }

    /**
     * @inheritDoc
     */
    public function isSkipped(): bool
    {
        return $this->isSkipped;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'canSign';
    }
}
