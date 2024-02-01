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
 * @since         2.5.0
 */
namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Controllers\Totp;

use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Duo\MfaDuoScenario;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Totp\MfaTotpOrganizationOnlyScenario;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Totp\MfaTotpScenario;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class TotpSetupGetControllerTest extends MfaIntegrationTestCase
{
    /**
     * @group mfa
     * @group mfaSetup
     * @group mfaSetupGet
     * @group mfaSetupGetTotp
     */
    public function testMfaSetupGetTotpNotAuthenticated()
    {
        $this->get('/mfa/setup/totp.json?api-version=v2');
        $this->assertResponseError('You need to login to access this location.');
    }

    /**
     * @group mfa
     * @group mfaSetup
     * @group mfaSetupGet
     * @group mfaSetupGetTotp
     */
    public function testMfaSetupGetTotpAlreadyConfigured()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaTotpScenario::class, $user);
        $this->mockMfaCookieValid($this->makeUac($user), MfaSettings::PROVIDER_TOTP);
        $this->get('/mfa/setup/totp');
        $this->assertResponseOk();
        $this->assertResponseContains('is enabled');
    }

    /**
     * @group mfa
     * @group mfaSetup
     * @group mfaSetupGet
     * @group mfaSetupGetTotp
     */
    public function testMfaSetupGetTotpOrgSettingsNotEnabled()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $this->mockMfaCookieValid($this->makeUac($user), MfaSettings::PROVIDER_DUO);
        $this->get('/mfa/setup/totp');
        $this->assertResponseError();
        $this->assertResponseContains('This authentication provider is not enabled for your organization.');
    }

    /**
     * @group mfa
     * @group mfaSetup
     * @group mfaSetupGet
     * @group mfaSetupGetTotp
     */
    public function testMfaSetupGetTotpSuccess()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaTotpOrganizationOnlyScenario::class);
        $this->get('/mfa/setup/totp');
        $this->assertResponseOk();
        $this->assertResponseContains('<form');
        $this->assertResponseContains('<svg');
    }

    /**
     * @group mfa
     * @group mfaSetup
     * @group mfaSetupGet
     * @group mfaSetupGetTotp
     */
    public function testMfaSetupGetTotpSuccessJson()
    {
        $this->logInAsUser();
        $this->loadFixtureScenario(MfaTotpOrganizationOnlyScenario::class);
        $this->getJson('/mfa/setup/totp.json?api-version=v2');
        $this->assertResponseOk();
        $this->assertNotEmpty($this->_responseJsonBody->otpQrCodeSvg);
        $this->assertNotEmpty($this->_responseJsonBody->otpProvisioningUri);
    }
}