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
 * @since         3.3.0
 */
namespace App\Model\Traits\Cleanup;

trait AvatarsCleanupTrait
{
    use TableCleanupTrait;

    /**
     * Delete all records where associated users are soft deleted
     *
     * @param bool|null $dryRun false
     * @return int number of affected records
     */
    public function cleanupSoftDeletedUsers(?bool $dryRun = false): int
    {
        return $this->cleanupSoftDeleted('Profiles.Users', $dryRun);
    }

    /**
     * Delete all records where associated users are deleted
     *
     * @param bool|null $dryRun false
     * @return int number of affected records
     */
    public function cleanupHardDeletedUsers(?bool $dryRun = false): int
    {
        return $this->cleanupHardDeleted('Profiles.Users', $dryRun);
    }

    /**
     * Delete all records where associated users are deleted
     *
     * @param bool|null $dryRun false
     * @return int number of affected records
     */
    public function cleanupHardDeletedProfiles(?bool $dryRun = false): int
    {
        return $this->cleanupHardDeleted('Profiles', $dryRun);
    }
}
