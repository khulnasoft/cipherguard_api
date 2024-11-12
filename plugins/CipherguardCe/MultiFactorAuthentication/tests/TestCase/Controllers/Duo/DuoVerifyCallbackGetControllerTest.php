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
namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Controllers\Duo;

use App\Model\Entity\AuthenticationToken;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Test\Factory\OrganizationSettingFactory;
use App\Utility\UuidFactory;
use Duo\DuoUniversal\Client;
use Cipherguard\MultiFactorAuthentication\Controller\Duo\DuoSetupGetController;
use Cipherguard\MultiFactorAuthentication\Service\Duo\MfaDuoStateCookieService;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\Test\Mock\DuoSdkClientMock;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Duo\MfaDuoScenario;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;
use Cipherguard\MultiFactorAuthentication\Utility\MfaVerifiedCookie;

class DuoVerifyCallbackGetControllerTest extends MfaIntegrationTestCase
{
    public function testDuoVerifyCallbackGetController_Error_NotAuthenticated()
    {
        $this->get('/mfa/verify/duo/callback');
        $this->assertRedirect();
        $this->assertRedirectContains('/auth/login?redirect=%2Fmfa%2Fverify%2Fduo%2Fcallback');
    }

    public function testDuoVerifyCallbackGetController_Error_JsonNotAllowed()
    {
        $user = $this->logInAsUser();
        $this->mockMfaCookieValid($this->makeUac($user), MfaSettings::PROVIDER_DUO);
        $this->get('/mfa/verify/duo/callback.json');
        $this->assertResponseError('You need to login to access this location.');
    }

    public function testDuoVerifyCallbackGetController_Error_AlreadyVerified()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $this->mockMfaCookieValid($this->makeUac($user), MfaSettings::PROVIDER_DUO);
        $this->get('/mfa/verify/duo/callback');
        $this->assertResponseError('The multi-factor authentication is not required.');
        $this->assertSame(1, OrganizationSettingFactory::count());
    }

    public function testDuoVerifyCallbackGetController_Error_InvalidOrgSettings()
    {
        $this->logInAsUser();
        $this->get('/mfa/verify/duo/callback');
        $this->assertRedirect();
        $this->assertRedirectContains('/');
    }

    public function testDuoVerifyCallbackGetController_Error_DuoStateCookieMissing()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $userId = $user->get('id');
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => '',
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();

        $this->get('/mfa/verify/duo/callback?state=' . $duoState . '&duo_code=' . UuidFactory::uuid());
        $this->assertResponseCode(400);
        $this->assertResponseContains('A Duo state cookie is required.');

        $this->assertCookieNotSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }

    public function testDuoVerifyCallbackGetController_Error_UnableToAuthenticateToDuo()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $userId = $user->get('id');
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        $authToken = AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => '',
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();
        $token = $authToken->token;

        $this->cookie(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE, $token);

        $this->get('/mfa/verify/duo/callback?error=DuoCallbackError&DuoCallbackErrorDescription');
        $this->assertResponseCode(400);
        $this->assertResponseContains('Unable to authenticate to Duo.');

        $this->assertCookieNotSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }

    public function testDuoSetupCallbackGetController_Error_With_Redirect()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $redirect = '/app';
        $userId = $user->get('id');
        $error = 'DuoCallbackError';
        $errorDesc = 'DuoCallbackErrorDescription';
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        $authToken = AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => $redirect,
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();
        $token = $authToken->token;

        $this->cookie(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE, $token);

        $this->get("/mfa/verify/duo/callback?error={$error}&error_description={$errorDesc}");
        $this->assertRedirect();
        $this->assertRedirectContains('/mfa/verify/duo?redirect=' . $redirect);
        $flashElement = $this->getSession()->read('Flash')['flash'][0];
        $this->assertEquals($flashElement['message'], "Unable to authenticate to Duo. {$error}: {$errorDesc}");

        $this->assertCookieNotSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }

    public function testDuoSetupCallbackGetController_Error_With_Wrong_Redirect()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $redirect = 'wwww.evil.com';
        $userId = $user->get('id');
        $error = 'DuoCallbackError';
        $errorDesc = 'DuoCallbackErrorDescription';
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        $authToken = AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => $redirect,
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();
        $token = $authToken->token;

        $this->cookie(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE, $token);

        $this->get("/mfa/verify/duo/callback?error={$error}&error_description={$errorDesc}");
        $this->assertRedirect();
        $this->assertRedirectContains('/mfa/verify/duo?redirect=/');
        $flashElement = $this->getSession()->read('Flash')['flash'][0];
        $this->assertEquals($flashElement['message'], "Unable to authenticate to Duo. {$error}: {$errorDesc}");

        $this->assertCookieNotSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }

    public function testDuoVerifyCallbackGetController_Error_CouldNotValidateDuoCallbackData()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $userId = $user->get('id');
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        $authToken = AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => '',
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();
        $token = $authToken->token;

        $this->cookie(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE, $token);

        $this->get('/mfa/verify/duo/callback');
        $this->assertResponseCode(400);
        $this->assertResponseContains('Unable to validate the Duo callback data.');

        $this->assertCookieNotSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }

    public function testDuoVerifyCallbackGetController_Success()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $userId = $user->get('id');
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        $authToken = AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => '',
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();
        $token = $authToken->token;

        $this->cookie(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE, $token);

        $this->get('/mfa/verify/duo/callback?state=' . $duoState . '&duo_code=' . UuidFactory::uuid());
        $this->assertResponseOk();

        $this->assertCookieSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }

    public function testDuoVerifyCallbackGetController_Success_Redirect()
    {
        $user = $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $duoState = UuidFactory::uuid();
        $userId = $user->get('id');
        $this->mockService(Client::class, function () use ($user) {
            return DuoSdkClientMock::createDefault($this, $user)->getClient();
        });

        $authToken = AuthenticationTokenFactory::make()->active()->data([
            'provider' => 'duo',
            'state' => $duoState,
            'redirect' => DuoSetupGetController::DUO_SETUP_REDIRECT_PATH,
            'user_agent' => 'CipherguardUA',
        ])->userId($userId)->type(AuthenticationToken::TYPE_MFA_VERIFY)->persist();

        $this->cookie(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE, $authToken->token);

        $this->get('/mfa/verify/duo/callback?state=' . $duoState . '&duo_code=' . UuidFactory::uuid());
        $this->assertResponseCode(302);

        $this->assertCookieSet(MfaVerifiedCookie::MFA_COOKIE_ALIAS);
        $this->assertCookieNotSet(MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
    }
}
