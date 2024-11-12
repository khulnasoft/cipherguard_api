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
 * @since         3.11.0
 */

namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Service\Duo;

use App\Model\Entity\AuthenticationToken;
use App\Model\Entity\Role;
use App\Test\Factory\UserFactory;
use App\Utility\UserAccessControl;
use Cake\Core\Configure;
use Cake\Http\Exception\UnauthorizedException;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\MultiFactorAuthentication\Service\Duo\MfaDuoVerifyDuoCodeService;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaOrgSettingsTestTrait;
use Cipherguard\MultiFactorAuthentication\Test\Mock\DuoSdkClientMock;

class MfaDuoVerifyDuoCodeServiceTest extends TestCase
{
    use TruncateDirtyTables;
    use MfaOrgSettingsTestTrait;

    public function testMfaDuoVerifyDuoCodeService_Success()
    {
        $settings = $this->getDefaultMfaOrgSettings();
        $this->mockMfaOrgSettings($settings);
        $user = UserFactory::make()->persist();
        $uac = new UserAccessControl(Role::USER, $user->id, $user->username);
        $duoCode = 'not-so-random-duo-code';

        $duoSdkClientMock = DuoSdkClientMock::createDefault($this, $user)->getClient();
        $service = new MfaDuoVerifyDuoCodeService(AuthenticationToken::TYPE_MFA_VERIFY, $duoSdkClientMock);
        $verified = $service->verify($uac, $duoCode);

        $this->assertTrue($verified);
    }

    public function testMfaDuoVerifyDuoCodeService_Error_CannotRetrieveAuthenticationDetails()
    {
        $settings = $this->getDefaultMfaOrgSettings();
        $this->mockMfaOrgSettings($settings);
        $user = UserFactory::make()->persist();
        $uac = new UserAccessControl(Role::USER, $user->id, $user->username);
        $duoCode = 'not-so-random-duo-code';

        $duoSdkClientMock = DuoSdkClientMock::createWithExchangeAuthorizationCodeFor2FAResultThrowingException($this);
        $service = new MfaDuoVerifyDuoCodeService(AuthenticationToken::TYPE_MFA_VERIFY, $duoSdkClientMock->getClient());

        try {
            $service->verify($uac, $duoCode);
        } catch (\Throwable $th) {
        }

        $this->assertInstanceOf(UnauthorizedException::class, $th);
        $this->assertTextContains('Unable to verify Duo code against Duo service', $th->getMessage());
    }

    public function testMfaDuoVerifyDuoCodeService_Error_CallbackWrongIss()
    {
        $settings = $this->getDefaultMfaOrgSettings();
        $this->mockMfaOrgSettings($settings);
        $user = UserFactory::make()->persist();
        $uac = new UserAccessControl(Role::USER, $user->id, $user->username);
        $duoCode = 'not-so-random-duo-code';

        $mock = DuoSdkClientMock::createWithWrongExchangeAuthorizationCodeFor2FAResultIss($this, $user);
        $service = new MfaDuoVerifyDuoCodeService(AuthenticationToken::TYPE_MFA_VERIFY, $mock->getClient());

        try {
            $service->verify($uac, $duoCode);
        } catch (\Throwable $th) {
        }

        $this->assertInstanceOf(UnauthorizedException::class, $th);
        $this->assertTextContains('The duo authentication origin endpoint does not match the organization setting duo hostname.', $th->getMessage());
    }

    public function testMfaDuoVerifyDuoCodeService_Success_CallbackWrongUsername_Without_Config()
    {
        $settings = $this->getDefaultMfaOrgSettings();
        $this->mockMfaOrgSettings($settings);
        $user = UserFactory::make()->persist();
        $uac = new UserAccessControl(Role::USER, $user->id, $user->username);
        $duoCode = 'not-so-random-duo-code';

        $duoSdkClientMock = DuoSdkClientMock::createWithWrongExchangeAuthorizationCodeFor2FAResultSub($this);
        $service = new MfaDuoVerifyDuoCodeService(AuthenticationToken::TYPE_MFA_VERIFY, $duoSdkClientMock->getClient());
        $verified = $service->verify($uac, $duoCode);

        $this->assertTrue($verified);
    }

    public function testMfaDuoVerifyDuoCodeService_Success_CallbackUsernameWithCaseDifference_With_Verify_Config_Enabled()
    {
        $settings = $this->getDefaultMfaOrgSettings();
        $this->mockMfaOrgSettings($settings);
        $user = UserFactory::make()->persist();
        $uac = new UserAccessControl(Role::USER, $user->id, $user->username);
        $duoCode = 'not-so-random-duo-code';

        Configure::write(MfaDuoVerifyDuoCodeService::CIPHERGUARD_SECURITY_MFA_DUO_VERIFY_SUBSCRIBER, true);

        $duoSdkClientMock = DuoSdkClientMock::createWithExchangeAuthorizationCodeFor2FAResultSub($this, mb_strtoupper($user->username));
        $service = new MfaDuoVerifyDuoCodeService(AuthenticationToken::TYPE_MFA_VERIFY, $duoSdkClientMock->getClient());
        $verified = $service->verify($uac, $duoCode);
        $this->assertTrue($verified);
    }

    public function testMfaDuoVerifyDuoCodeService_Error_CallbackWrongUsername_With_Verify_Config_Enabled()
    {
        $settings = $this->getDefaultMfaOrgSettings();
        $this->mockMfaOrgSettings($settings);
        $user = UserFactory::make()->persist();
        $uac = new UserAccessControl(Role::USER, $user->id, $user->username);
        $duoCode = 'not-so-random-duo-code';

        Configure::write(MfaDuoVerifyDuoCodeService::CIPHERGUARD_SECURITY_MFA_DUO_VERIFY_SUBSCRIBER, true);

        $duoSdkClientMock = DuoSdkClientMock::createWithWrongExchangeAuthorizationCodeFor2FAResultSub($this);
        $service = new MfaDuoVerifyDuoCodeService(AuthenticationToken::TYPE_MFA_VERIFY, $duoSdkClientMock->getClient());

        try {
            $service->verify($uac, $duoCode);
        } catch (\Throwable $th) {
        }

        $this->assertInstanceOf(UnauthorizedException::class, $th);
        $this->assertTextContains('The duo authentication subscriber does not match the operator username.', $th->getMessage());
    }
}
