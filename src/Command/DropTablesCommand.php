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
 * @since         2.0.0
 */
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

class DropTablesCommand extends CipherguardCommand
{
    use DatabaseAwareCommandTrait;

    /**
     * @inheritDoc
     */
    public static function getCommandDescription(): string
    {
        return __('Drop all the tables. Dangerous but useful for a full reinstall.');
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addDatasourceOption($parser, false);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        parent::execute($args, $io);

        $datasource = $args->getOption('datasource');
        $connection = ConnectionManager::get($datasource);
        $tables = ConnectionManager::get($datasource)->getSchemaCollection()->listTables();
        foreach ($tables as $table) {
            $io->out(__('Dropping table ' . $table));
            $quotedTableName = $connection->getDriver()->quoteIdentifier($table);
            $connection->execute("drop table {$quotedTableName};");
        }
        $this->success(__('{0} tables dropped', count($tables)), $io);

        return $this->successCode();
    }
}
