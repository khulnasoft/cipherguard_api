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

namespace App\Service\Subscriptions;

use App\Command\CipherguardCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

interface SubscriptionCheckInCommandServiceInterface
{
    /**
     * Checks if the cipherguard subscription is valid.
     *
     * @param \App\Command\CipherguardCommand $command The command requesting the check.
     * @param \Cake\Console\Arguments $args The arguments passed to the $command
     * @param \Cake\Console\ConsoleIo $io Console IO.
     * @return bool
     */
    public function check(CipherguardCommand $command, Arguments $args, ConsoleIo $io): bool;
}
