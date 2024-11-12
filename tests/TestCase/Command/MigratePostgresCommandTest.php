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
 * @since         3.5.0
 */
namespace App\Test\TestCase\Command;

use App\Command\MigratePostgresCommand;
use App\Service\Command\ProcessUserService;
use App\Test\Lib\AppTestCase;
use App\Test\Lib\Utility\CipherguardCommandTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Database\Driver\Postgres;
use Cake\Datasource\ConnectionManager;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * App\Command\PostgresMigrationCommand Test Case
 *
 * @uses \App\Command\MigratePostgresCommand
 */
class MigratePostgresCommandTest extends AppTestCase
{
    use ConsoleIntegrationTestTrait;
    use CipherguardCommandTestTrait;
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
        $this->mockProcessUserService('www-data');
    }

    protected function countMigrations(): int
    {
        return (int)ConnectionManager::get('test')->selectQuery()
            ->select('COUNT(*)')
            ->from('phinxlog')
            ->execute()
            ->fetch()[0];
    }

    /**
     * Basic help test
     */
    public function testPostgresMigrateCommandHelp()
    {
        $this->exec('cipherguard migrate_postgres -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Re-runs the migrations required by Postgres.');
        $this->assertOutputContains('cake cipherguard migrate_postgres');
    }

    public function testPostgresMigrateCommandAsRoot()
    {
        $this->assertCommandCannotBeRunAsRootUser('migrate_postgres');
    }

    /**
     * Run the command and ensure that all migrations are still in place.
     */
    public function testPostgresMigrateCommandAsNonRoot()
    {
        $nMigrations = $this->countMigrations();
        // Ensure that the count migration is returning some integer, and not a fake 1 or something.
        $this->assertGreaterThan(50, $nMigrations);

        $this->exec('cipherguard migrate_postgres -d test -q');
        if (ConnectionManager::get('test')->getDriver() instanceof Postgres) {
            $this->assertExitSuccess('Cipherguard can now be used with Postgres.');
        } else {
            $this->assertExitError('This command is available with a Postgres connection only.');
        }

        $this->assertSame($nMigrations, $this->countMigrations());
    }

    /**
     * Ensures that a limited amount of migrations is impacted by the command.
     */
    public function testPostgresMigrateCommand_DeletePostgresRelevantMigrations()
    {
        $cmd = new MigratePostgresCommand(new ProcessUserService());
        $connection = ConnectionManager::get('test');
        $count = $this->countMigrations();
        $cmd->deletePostgresRelevantMigrations($connection);
        $newCount = $this->countMigrations();
        $diff = $count - $newCount;

        $this->assertLessThanOrEqual(count($cmd::POSTGRES_RELEVANT_MIGRATIONS), $diff);
        $this->assertGreaterThanOrEqual(2, $diff);

        // Re-run the migrations to complete the schema again
        $this->exec('migrations migrate -q -c test --no-lock');
        $this->assertSame($count, $this->countMigrations());
    }
}
