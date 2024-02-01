<?php
declare(strict_types=1);

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
 * @since         4.3.0
 */

namespace App\Service\Setup;

class DefaultRecoverStartService extends AbstractRecoverStartService
{
    /**
     * @param \App\Service\Setup\RecoverStartUserInfoService $recoverStartUserInfoService User info service.
     */
    public function __construct(RecoverStartUserInfoService $recoverStartUserInfoService)
    {
        $this->add($recoverStartUserInfoService);
    }
}
