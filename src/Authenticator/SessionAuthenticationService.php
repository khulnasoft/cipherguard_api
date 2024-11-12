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
 * @since         3.3.1
 */
namespace App\Authenticator;

use Authentication\AuthenticationService;

class SessionAuthenticationService extends AuthenticationService
{
    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        // Load the default authenticators. Session should be first.
        $this->loadAuthenticator('Authentication.Session');
        $this->loadAuthenticator('Gpg');
    }
}
