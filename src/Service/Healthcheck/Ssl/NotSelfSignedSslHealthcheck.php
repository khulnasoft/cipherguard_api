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

namespace App\Service\Healthcheck\Ssl;

class NotSelfSignedSslHealthcheck extends AbstractBaseSslHealthcheck
{
    /**
     * @inheritDoc
     */
    protected array $helpMessage = [
        'Check https://help.cipherguard.github.io/faq/hosting/troubleshoot-ssl',
    ];

    /**
     * @inheritDoc
     */
    protected function getClientOptions(): array
    {
        return [
            'ssl_verify_peer' => true,
            'ssl_verify_host' => true,
            'ssl_allow_self_signed' => false,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('Not using a self-signed certificate.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('Using a self-signed certificate.');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'notSelfSigned';
    }
}
