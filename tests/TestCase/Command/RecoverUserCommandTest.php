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
 * @since         4.0.0
 */
namespace App\Test\TestCase\Command;

use App\Model\Entity\AuthenticationToken;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\Utility\CipherguardCommandTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class RecoverUserCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;
    use TruncateDirtyTables;
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
        $this->mockProcessUserService('www-data');
    }

    public function testRecoverUserCommandHelp()
    {
        $this->exec('cipherguard recover_user -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Get an existing account recovery token, or create a new one.');
        $this->assertOutputContains('--username, -u');
        $this->assertOutputContains('The user name (email).');
        $this->assertOutputContains('--create, -c');
        $this->assertOutputContains('Create a new token.');
    }

    public function testRecoverUserCommand_Fetch_On_Active_User()
    {
        [$user] = UserFactory::make(2)->user()->active()->persist();
        $expirationDate = Configure::read('cipherguard.auth.token.' . AuthenticationToken::TYPE_RECOVER . '.expiry');
        $activeNonExpiredToken = AuthenticationTokenFactory::make()
            ->type(AuthenticationToken::TYPE_RECOVER)
            ->userId($user->id)
            ->active()
            ->created(FrozenDate::parse('tomorrow - ' . $expirationDate))
            ->persist();
        $this->exec('cipherguard recover_user -u ' . $user->username);
        $this->assertExitSuccess();
        $this->assertOutputContains(
            Router::url('/setup/recover/start/' . $user->id . '/' . $activeNonExpiredToken['token'], true)
        );
        $this->assertSame(1, AuthenticationTokenFactory::count());
    }

    public function testRecoverUserCommand_Create_On_Active_User_Without_Token()
    {
        [$user] = UserFactory::make(2)->user()->active()->persist();
        $this->exec('cipherguard recover_user -u ' . $user->username);
        $this->assertExitError();
        $this->assertOutputContains("An active recovery token could not be found for the user {$user->username}.");
        $this->assertSame(0, AuthenticationTokenFactory::count());
    }

    public function testRecoverUserCommand_Create_On_Active_User()
    {
        [$user] = UserFactory::make(2)->user()->active()->persist();
        $this->exec('cipherguard recover_user -c -u ' . $user->username);
        $this->assertExitSuccess();
        $token = AuthenticationTokenFactory::find()->firstOrFail();
        $this->assertOutputContains(
            Router::url('/setup/recover/start/' . $user->id . '/' . $token['token'], true)
        );
        $this->assertSame(1, AuthenticationTokenFactory::count());
    }

    public function testRecoverUserCommand_Create_On_Deleted_User()
    {
        $user = UserFactory::make()->user()->active()->deleted()->persist();
        $this->exec('cipherguard recover_user -c -u ' . $user->username);
        $this->assertExitError();
        $this->assertErrorContains('The user does not exist or is not active or is disabled.');
    }

    public function testRecoverUserCommand_Create_On_Disabled_User()
    {
        $user = UserFactory::make()->user()->active()->disabled()->persist();
        $this->exec('cipherguard recover_user -c -u ' . $user->username);
        $this->assertExitError();
        $this->assertErrorContains('The user does not exist or is not active or is disabled.');
    }

    public function testRecoverUserCommand_On_Inactive_User()
    {
        $user = UserFactory::make()->inactive()->user()->persist();
        $this->exec('cipherguard recover_user -u ' . $user->username);
        $this->assertExitError();
        $this->assertErrorContains('The user does not exist or is not active or is disabled.');
    }

    public function testRecoverUserCommand_On_Expired_Token()
    {
        $user = UserFactory::make()->user()->active()->persist();
        AuthenticationTokenFactory::make()
            ->type(AuthenticationToken::TYPE_RECOVER)
            ->userId($user->id)
            ->active()
            ->created(FrozenDate::now()->subDays(100))
            ->persist();
        $this->exec('cipherguard recover_user -u ' . $user->username);
        $this->assertExitError();
        $this->assertOutputContains('You may create one using the option --create.');
        $this->assertOutputContains("An active recovery token could not be found for the user {$user->username}.");
    }

    public function testRecoverUserCommand_WithSameUsernamePresent(): void
    {
        $username = 'ada@cipherguard.github.io';
        // Create two users with same username.
        // 1. First would be active & deleted
        // 2. Second with active & not delete(`deleted=0`)
        UserFactory::make(compact('username'), 10)->user()->active()->deleted()->persist();
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make(compact('username'))->user()->active()->persist();

        $this->exec('cipherguard recover_user -c -u ' . $user->username);

        $this->assertExitSuccess();
        $token = AuthenticationTokenFactory::firstOrFail();
        $this->assertOutputContains(
            Router::url('/setup/recover/start/' . $user->id . '/' . $token['token'], true)
        );
    }
}
