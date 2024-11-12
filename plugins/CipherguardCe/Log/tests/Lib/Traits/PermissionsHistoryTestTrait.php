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
 */
namespace Cipherguard\Log\Test\Lib\Traits;

trait PermissionsHistoryTestTrait
{
    public function assertPermissionHistoryExists($conditions)
    {
        $permissionHistory = $this->PermissionsHistory
            ->find()
            ->where($conditions)
            ->first();
        $this->assertNotEmpty($permissionHistory, 'No corresponding permissionsHistory could be found');

        return $permissionHistory;
    }

    public function assertPermissionsHistoryCount($expectedCount)
    {
        $entityHistoryCount = $this->PermissionsHistory
            ->find()
            ->count();
        $this->assertEquals($expectedCount, $entityHistoryCount);
    }

    public function assertOnePermissionHistory()
    {
        $this->assertPermissionsHistoryCount(1);
    }

    public function assertPermissionsHistoryEmpty()
    {
        $this->assertPermissionsHistoryCount(0);
    }
}
