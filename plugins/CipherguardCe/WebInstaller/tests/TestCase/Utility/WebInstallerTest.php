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
namespace Cipherguard\WebInstaller\Test\TestCase\Utility;

use App\Model\Entity\AuthenticationToken;
use App\Model\Entity\Role;
use App\Test\Lib\Model\GpgkeysModelTrait;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\ORM\TableRegistry;
use Cipherguard\WebInstaller\Test\Lib\ConfigurationTrait;
use Cipherguard\WebInstaller\Test\Lib\DatabaseTrait;
use Cipherguard\WebInstaller\Test\Lib\WebInstallerIntegrationTestCase;
use Cipherguard\WebInstaller\Utility\DatabaseConfiguration;
use Cipherguard\WebInstaller\Utility\WebInstaller;

class WebInstallerTest extends WebInstallerIntegrationTestCase
{
    use ConfigurationTrait;
    use DatabaseTrait;
    use GpgkeysModelTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->skipTestIfNotWebInstallerFriendly();
        $this->backupConfiguration();
    }

    public function tearDown(): void
    {
        $this->restoreConfiguration();
        parent::tearDown();
    }

    public function testWebInstallerUtilityInitDatabaseConnectionSuccess()
    {
        $webInstaller = new WebInstaller(null);
        $databaseSettings = $this->getTestDatasourceFromConfig();
        $webInstaller->setSettings('database', $databaseSettings);
        $webInstaller->initDatabaseConnection();
        $connected = DatabaseConfiguration::testConnection();
        $this->assertTrue($connected);
    }

    public function testWebInstallerUtilityInitDatabaseConnectionError()
    {
        $webInstaller = new WebInstaller(null);
        $databaseSettings = $this->getTestDatasourceFromConfig();
        $databaseSettings['host'] = 'invalid-host';
        $webInstaller->setSettings('database', $databaseSettings);
        $webInstaller->initDatabaseConnection();
        $connected = DatabaseConfiguration::testConnection();
        $this->assertFalse($connected);
        $this->restoreTestConnection();
    }

    public function testWebInstallerUtilityGpgImportKeySuccess()
    {
        $webInstaller = new WebInstaller(null);
        $gpgSettings = $this->getDummyGpgkey();
        $webInstaller->setSettings('gpg', $gpgSettings);
        $webInstaller->importGpgKey();

        $gpgSettings = $webInstaller->getSettings('gpg');
        $this->assertNotNull($gpgSettings['fingerprint']);
        $this->assertEquals(file_get_contents(Configure::read('cipherguard.gpg.serverKey.public')), $gpgSettings['public_key_armored']);
        $this->assertEquals(file_get_contents(Configure::read('cipherguard.gpg.serverKey.private')), $gpgSettings['private_key_armored']);
        $this->assertFileExists(Configure::read('cipherguard.gpg.serverKey.public'));
        $this->assertFileExists(Configure::read('cipherguard.gpg.serverKey.private'));
    }

    public function testWebInstallerUtilityWriteCipherguardConfigFileSuccess()
    {
        $this->loadPlugins(['Cipherguard/WebInstaller' => []]);
        $webInstaller = new WebInstaller(null);

        // Add the database configuration.
        $databaseSettings = [
            'foo' => 'foo-value',
            'bar' => 'bar-value',
        ];
        $webInstaller->setSettings('database', $databaseSettings);

        // Add the gpg configuration to generate a new server key.
        $gpgSettings = $this->getDummyGpgkey();
        $webInstaller->setSettings('gpg', $gpgSettings);
        $webInstaller->importGpgKey();

        // Add the email configuration.
        $emailSettings = [
            'sender_name' => 'Cipherguard Test',
            'sender_email' => 'test@cipherguard.github.io',
            'host' => 'unreachable_host',
            'tls' => true,
            'port' => 587,
            'username' => 'test@cipherguard.github.io',
            'password' => 'password',
            'send_test_email' => true,
            'email_test_to' => 'test@cipherguard.github.io',
        ];
        $webInstaller->setSettings('email', $emailSettings);

        // Add the options configuration.
        $optionsSettings = [
            'full_base_url' => Configure::read('app.full_base_url'),
            'force_ssl' => 0,
        ];
        $webInstaller->setSettings('options', $optionsSettings);

        $testFile = TMP . 'test_cipherguard.php';
        $webInstaller->writeCipherguardConfigFile($testFile);
        $this->assertFileExists($testFile);
        $testFileContent = file_exists($testFile) ? include $testFile : [];
        $this->assertSame($databaseSettings, $testFileContent['Datasources']['default']);
        $this->assertFalse($testFileContent['cipherguard']['ssl']['force']);
        unlink($testFile);
    }

    public function testWebInstallerUtilityInstallDatabaseSuccessAndCreateFirstUserSuccess()
    {
        $this->loadPlugins(['Migrations' => []]);
        $webInstaller = new WebInstaller(null);
        $databaseSettings = $this->getTestDatasourceFromConfig();
        $webInstaller->setSettings('database', $databaseSettings);
        $webInstaller->initDatabaseConnection();
        $this->dropAllTables();
        $webInstaller->installDatabase();

        // Validate schema
        try {
            DatabaseConfiguration::validateSchema();
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
        $this->assertTrue(true);

        // Create first user
        $Users = TableRegistry::getTableLocator()->get('Users');
        $roleAdminId = $Users->Roles->getIdByName(Role::ADMIN);
        $userSettings = [
            'username' => 'aurore@cipherguard.github.io',
            'profile' => [
                'first_name' => 'Aurore',
                'last_name' => 'Avarguès-Weber',
            ],
            'deleted' => false,
            'role_id' => $roleAdminId,
        ];
        $webInstaller->setSettings('first_user', $userSettings);
        $webInstaller->createFirstUser();

        /** @var \App\Model\Entity\User $user */
        $user = $Users->find()
            ->where(['username' => 'aurore@cipherguard.github.io'])
            ->contain(['Profiles', 'AuthenticationTokens'])
            ->first();
        $this->assertEquals($userSettings['username'], $user->username);
        $this->assertEquals($roleAdminId, $user->role_id);
        $this->assertEquals(false, $user->deleted);
        $this->assertEquals(false, $user->active);
        $this->assertEquals($userSettings['profile']['first_name'], $user->profile->first_name);
        $this->assertEquals($userSettings['profile']['last_name'], $user->profile->last_name);
        $this->assertEquals(AuthenticationToken::TYPE_REGISTER, $user->authentication_tokens[0]->type);
    }
}
