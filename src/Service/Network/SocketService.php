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
 * @since         4.8.0
 */
namespace App\Service\Network;

use Cake\Network\Socket;

class SocketService
{
    /**
     * @param array $socketOptions Socket options.
     * @return bool Success
     * @throws \Cake\Network\Exception\SocketException
     */
    public function canConnect(array $socketOptions): bool
    {
        $socket = new Socket($socketOptions);

        return $socket->connect();
    }
}