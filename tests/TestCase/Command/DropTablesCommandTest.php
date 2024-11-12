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
 * @since         3.1.0
 */
namespace App\Test\TestCase\Command;

use App\Test\Lib\AppTestCase;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\MissingDatasourceConfigException;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class DropTablesCommandTest extends AppTestCase
{
    use ConsoleIntegrationTestTrait;
    use TruncateDirtyTables;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();
    }

    /**
     * Basic help test
     */
    public function testDropTablesCommandHelp()
    {
        $this->exec('cipherguard drop_tables -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Drop all the tables. Dangerous but useful for a full reinstall.');
        $this->assertOutputContains('cake cipherguard drop_tables');
    }

    /**
     * Basic test
     */
    public function testDropTablesCommand()
    {
        $this->exec('cipherguard drop_tables');
        $this->assertExitSuccess();

        // Assert that all tables were dropped.
        $tables = ConnectionManager::get('default')->getSchemaCollection()->listTables();
        $this->assertEmpty($tables);

        // Run migrations to recreate the lost tables.
        $this->exec('migrations migrate -c test -q --no-lock');
    }

    /**
     * Basic failing test
     */
    public function testDropTablesCommandWrongDataSource()
    {
        $this->expectException(MissingDatasourceConfigException::class);
        $this->exec('cipherguard drop_tables -d wrong_connection');
        $this->assertExitError();
    }
}
