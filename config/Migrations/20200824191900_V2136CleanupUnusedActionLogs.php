<?php
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
 * @since         2.13.0
 */

use App\Utility\UserAction;
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class V2136CleanupUnusedActionLogs extends AbstractMigration
{
    public function up()
    {
        $actionToCleanup = 'AuthIsAuthenticated.isAuthenticated';

        $actionLogsTable = TableRegistry::getTableLocator()->get('Cipherguard/Log.ActionLogs');
        $actionLogsTable->deleteAll([
            'action_id' => UserAction::actionId($actionToCleanup)
        ]);
    }
}
