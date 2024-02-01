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
 * @since         3.1.0
 */
namespace App\Test\TestCase\Command;

use App\Command\InstallCommand;
use App\Model\Entity\Role;
use App\Test\Lib\AppTestCase;
use App\Test\Lib\Model\EmailQueueTrait;
use App\Test\Lib\Utility\CipherguardCommandTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Faker\Factory;
use Cipherguard\EmailNotificationSettings\Test\Lib\EmailNotificationSettingsTestTrait;

class InstallCommandTest extends AppTestCase
{
    use ConsoleIntegrationTestTrait;
    use EmailNotificationSettingsTestTrait;
    use EmailQueueTrait;
    use CipherguardCommandTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();
        InstallCommand::$isUserRoot = false;
        $this->emptyDirectory(CACHE . 'database' . DS);
        $this->enableFeaturePlugin('JwtAuthentication');
        $this->loadNotificationSettings();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->disableFeaturePlugin('JwtAuthentication');
    }

    /**
     * Basic help test
     */
    public function testInstallCommandHelp()
    {
        $this->exec('cipherguard install -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Installation shell for the cipherguard application.');
        $this->assertOutputContains('cake cipherguard install');
    }

    /**
     * @Given I am root
     * @When I run "cipherguard migrate"
     * @Then the migrations cannot be run.
     */
    public function testInstallCommandAsRoot()
    {
        $this->assertCommandCannotBeRunAsRootUser(InstallCommand::class);
    }

    /**
     * Quick install with no existing backup
     */
    public function testInstallCommandQuickWithNoExistingBackup()
    {
        $this->exec('cipherguard install --quick -q');
        $this->assertExitError();
    }

    /**
     * Quick install with existing backup
     */
    public function testInstallCommandQuickWithExistingBackup()
    {
        // Create a backup
        $cmd = "
            INSERT INTO avatars (id, profile_id, created, modified)
            VALUES (
                '0da907bd-5c57-5acc-ba39-c6ebe091f613',
                '0da907bd-5c57-5acc-ba39-c6ebe091f613',
                '2021-03-25 05:48:54',
                '2021-03-25 05:48:54'
            );
        ";
        file_put_contents(CACHE . 'database' . DS . 'backup_foo.sql', $cmd);

        $this->exec('cipherguard install --quick -q');
        $this->assertExitSuccess();

        $this->assertSame(
            1,
            TableRegistry::getTableLocator()->get('Avatars')->find()->count()
        );
    }

    /**
     * Normal installation will fail because tables are present
     */
    public function testInstallCommandNormalWithExistingTables()
    {
        $this->exec('cipherguard install -q');
        $this->assertExitError();
    }

    /**
     * Normal installation force
     *
     * @group mysqldump
     */
    public function testInstallCommandNormalForceWithoutAdmin()
    {
        $this->exec('cipherguard install --force --no-admin --backup -q -d test');
        $this->assertExitSuccess();
    }

    /**
     * Normal installation force with data import
     *
     * @group mysqldump
     */
    public function testInstallCommandNormalForceWithDataImport()
    {
        $this->exec('cipherguard install --force --no-admin --backup -q -d test');
        $this->assertExitSuccess();
    }

    /**
     * Normal installation force with admin data
     *
     * @group mysqldump
     */
    public function testInstallCommandNormalForceWithAdminData()
    {
        $faker = Factory::create();
        $userName = $faker->email();
        $firstName = $faker->firstNameFemale();
        $lastName = $faker->lastName();
        $cmd = 'cipherguard install --force --backup -q ';
        $cmd .= ' --admin-first-name ' . $firstName;
        $cmd .= ' --admin-last-name ' . $lastName;
        $cmd .= ' --admin-username ' . $userName;
        $cmd .= ' -d test';

        $this->exec($cmd);
        $this->assertExitSuccess();

        $UsersTable = TableRegistry::getTableLocator()->get('Users');
        $admins = $UsersTable->find()
            ->contain('Profiles')
            ->innerJoinWith('Roles', function (Query $q) {
                return $q->where(['Roles.name' => Role::ADMIN]);
            });
        $this->assertSame(1, $admins->count());
        $admin = $admins->first();
        $this->assertSame($userName, $admin->get('username'));
        $this->assertSame($firstName, $admin->profile->first_name);
        $this->assertSame($lastName, $admin->profile->last_name);
        $this->assertFalse($admin->get('active'));
//         TODO: fix this line in the CI
//        $this->assertEmailQueueCount(1);
    }
}
