<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         3.1.0
 */
namespace App\Test\TestCase\Command;

use App\Command\CleanupCommand;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCase;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Migrations\TestSuite\Migrator;

class CleanupCommandTest extends AppTestCase
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

        CleanupCommand::resetCleanups();
    }

    /**
     * Basic help test
     */
    public function testCleanupCommandHelp()
    {
        $this->exec('cipherguard cleanup -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Cleanup and fix issues in database.');
        $this->assertOutputContains('cake cipherguard cleanup');
    }

    /**
     * Basic test
     */
    public function testCleanupCommandFixMode()
    {
        (new Migrator())->run();
        UserFactory::make()->admin()->persist();
        $this->exec('cipherguard cleanup');
        $this->assertExitSuccess();
        $this->assertOutputContains('Cleanup shell');
        $this->assertOutputContains('(fix mode)');
    }

    /**
     * Basic test dry run
     */
    public function testCleanupCommandDryRun()
    {
        UserFactory::make()->admin()->persist();
        $this->exec('cipherguard cleanup --dry-run');
        $this->assertExitSuccess();
        $this->assertOutputContains('Cleanup shell');
        $this->assertOutputContains('(dry-run)');
    }
}
