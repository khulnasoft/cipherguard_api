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
 * @since         2.5.0
 */
namespace Cipherguard\WebInstaller\Test\Lib;

use Cake\Datasource\ConnectionManager;

trait DatabaseTrait
{
    /**
     * Drops all the database tables.
     */
    public static function dropAllTables()
    {
        $connection = ConnectionManager::get('default');
        $tables = ConnectionManager::get('default')->getSchemaCollection()->listTables();
        foreach ($tables as $table) {
            $quotedTableName = $connection->getDriver()->quoteIdentifier($table);
            $connection->query("DROP TABLE {$quotedTableName}");
        }
    }
}
