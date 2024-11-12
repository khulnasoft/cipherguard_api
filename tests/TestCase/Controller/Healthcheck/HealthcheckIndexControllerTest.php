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
namespace App\Test\TestCase\Controller\Healthcheck;

use App\Controller\Healthcheck\HealthcheckIndexController;
use App\Model\Validation\EmailValidationRule;
use App\Service\Healthcheck\Environment\NextMinPhpVersionHealthcheck;
use App\Test\Factory\RoleFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Utility\HealthcheckRequestTestTrait;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Client;
use Cake\Http\TestSuite\HttpClientTrait;
use Cipherguard\SmtpSettings\Middleware\SmtpSettingsSecurityMiddleware;
use Cipherguard\SmtpSettings\SmtpSettingsPlugin;

/**
 * @covers \App\Controller\Healthcheck\HealthcheckIndexController
 */
class HealthcheckIndexControllerTest extends AppIntegrationTestCase
{
    use HealthcheckRequestTestTrait;
    use HttpClientTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockService(Client::class, function () {
            return $this->getMockedHealthcheckStatusRequest();
        });
        $this->mockService('fullBaseUrlReachableClient', function () {
            return $this->getMockedHealthcheckStatusRequest(
                200,
                json_encode(['body' => 'OK'])
            );
        });
        $this->mockService('sslHealthcheckClient', function () {
            return $this->getMockedHealthcheckStatusRequest();
        });
    }

    public function testHealthcheckIndexController_No_Admin_Should_Throw_Exception(): void
    {
        $this->logInAsUser();
        $this->getJson('/healthcheck.json');
        $this->assertForbiddenError('Access restricted to administrators.');
    }

    public function testHealthcheckIndexController_Success_Html(): void
    {
        $this->logInAsAdmin();
        $this->get('/healthcheck');
        $this->assertResponseContains('Cipherguard API Status');
        $this->assertResponseOk();
    }

    /**
     * Strangely, the status returned is OK, although the healthcheck failed
     * Leaving the test as documentation
     */
    public function testHealthcheckIndexController_ErrorNotReachable_Html(): void
    {
        $this->logInAsAdmin();
        $this->mockService(Client::class, function () {
            return $this->getMockedHealthcheckStatusRequest(400);
        });

        $this->get('/healthcheck');

        $this->assertResponseContains('Cipherguard API Status');
        $this->assertResponseContains('Environment');
        $this->assertResponseContains('Config files');
        $this->assertResponseContains('Core config');
        $this->assertResponseContains('SMTP settings');
        $this->assertResponseContains('Application configuration');
        $this->assertResponseContains('Database');
        $this->assertResponseContains('GPG Configuration');
        $this->assertResponseNotContains('JWT Authentication');
        $this->assertResponseContains('SSL Certificate');
        $this->assertResponseOk();
    }

    public function testHealthcheckIndexController_Success_Json(): void
    {
        $this->enableFeaturePlugin(SmtpSettingsPlugin::class);
        RoleFactory::make(3)->persist();
        $this->logInAsAdmin();
        $this->getJson('/healthcheck.json');

        $this->assertResponseSuccess();
        $connection = ConnectionManager::get('default');
        $tableCount = count($connection->getSchemaCollection()->listTables());
        $result = $this->getResponseBodyAsArray();
        $https = strpos(Configure::read('App.fullBaseUrl'), 'https') === 0;
        $expectedResponse = [
            'ssl' => [
                'peerValid' => true,
                'hostValid' => true,
                'notSelfSigned' => true,
                //'is' => false, // TODO: Currently not in response
            ],
            'database' => [
                'tablesCount' => true,
                'info' => ['tablesCount' => $tableCount],
                'connect' => true,
                'supportedBackend' => true,
                'defaultContent' => true,
            ],
            'application' => [
                'info' => [
                    'remoteVersion' => 'undefined', // value mismatch - 4.5.2
                    'currentVersion' => Configure::read('cipherguard.version'),
                ],
                'latestVersion' => null, // value mismatch - false
                'schema' => true,
                'robotsIndexDisabled' => true,
                'sslForce' => false,
                'sslFullBaseUrl' => ($https !== false),
                'configPath' => CONFIG . 'cipherguard.php',
                'seleniumDisabled' => !Configure::read('cipherguard.selenium.active'),
                'registrationClosed' => [
                    'isSelfRegistrationPluginEnabled' => $this->isFeaturePluginEnabled('SelfRegistration'),
                    'selfRegistrationProvider' => null,
                    'isRegistrationPublicRemovedFromCipherguard' => is_null(Configure::read('cipherguard.registration.public')),
                ],
                'hostAvailabilityCheckEnabled' => Configure::read(EmailValidationRule::MX_CHECK_KEY),
                'jsProd' => (Configure::read('cipherguard.js.build') === 'production'),
                'emailNotificationEnabled' => false,
            ],
            'gpg' => [
                'canDecryptVerify' => true,
                'canVerify' => true,
                'gpgKeyPublicInKeyring' => true,
                'canEncrypt' => true,
                'canDecrypt' => true,
                'canEncryptSign' => true,
                'canSign' => true,
                'gpgHome' => true,
                'gpgKeyPrivateFingerprint' => true,
                'gpgKeyPublicFingerprint' => true,
                'gpgKeyPublicEmail' => true,
                'gpgKeyPublicReadable' => true,
                'gpgKeyPrivateReadable' => true,
                'gpgKey' => true,
                'lib' => true,
                'gpgKeyNotDefault' => false,
                'info' => [
                    'gpgHome' => '/root/.gnupg',
                    'gpgKeyPrivate' => Configure::read('cipherguard.gpg.serverKey.private'),
                ],
                'gpgHomeWritable' => true,
                'gpgKeyPublic' => true,
                'gpgKeyPublicBlock' => true,
                'gpgKeyPrivate' => (Configure::read('cipherguard.gpg.serverKey.private') !== null),
                'gpgKeyPrivateBlock' => true,
                'isPublicServerKeyGopengpgCompatible' => true,
                'isPrivateServerKeyGopengpgCompatible' => true,
            ],
            'environment' => [
                'phpVersion' => true,
                'nextMinPhpVersion' => version_compare(
                    PHP_VERSION,
                    Configure::read(NextMinPhpVersionHealthcheck::PHP_NEXT_MIN_VERSION_CONFIG),
                    '>='
                ),
                'info' => ['phpVersion' => PHP_VERSION],
                'pcre' => true,
                'mbstring' => true,
                'gnupg' => true,
                'intl' => true,
                'image' => true,
                'tmpWritable' => true,
                'logWritable' => true,
            ],
            'configFile' => [
                'app' => true,
                'cipherguard' => (file_exists(CONFIG . 'cipherguard.php')),
            ],
            'core' => [
                'cache' => true,
                'debugDisabled' => false,
                'salt' => true,
                'fullBaseUrl' => true,
                'validFullBaseUrl' => true,
                'info' => ['fullBaseUrl' => Configure::read('App.fullBaseUrl')],
                'fullBaseUrlReachable' => true,
            ],
            'smtpSettings' => [
                'isEnabled' => $this->isFeaturePluginEnabled('SmtpSettings'),
                'areEndpointsDisabled' => Configure::read(SmtpSettingsSecurityMiddleware::CIPHERGUARD_SECURITY_SMTP_SETTINGS_ENDPOINTS_DISABLED),
                'errorMessage' => false,
                'source' => 'env variables',
                'isInDb' => false,
                'customSslOptions' => true,
            ],
        ];
        $this->assertArrayEqualsCanonicalizing($expectedResponse, $result);
    }

    /**
     * Throw a forbidden error if the endpoint is disabled
     */
    public function testHealthcheckIndexController_ErrorEndpointDisabled_Json(): void
    {
        $this->logInAsAdmin();
        Configure::write(
            HealthcheckIndexController::CIPHERGUARD_PLUGINS_HEALTHCHECK_SECURITY_INDEX_ENDPOINT_ENABLED,
            false
        );
        $this->logInAsAdmin();

        $this->getJson('/healthcheck.json');

        $this->assertForbiddenError('Healthcheck security index endpoint disabled.');
        // Assert body
        $resultBody = $this->_responseJsonBody;
        $this->assertSame('', $resultBody);
        // Assert headers
        $resultHeader = $this->_responseJsonHeader;
        $expectedHeaderAttributes = ['id', 'status', 'servertime', 'action', 'message', 'url', 'code'];
        $this->assertObjectHasAttributes($expectedHeaderAttributes, $resultHeader);
        $this->assertSame('error', $resultHeader->status);
        $this->assertSame('/healthcheck.json', $resultHeader->url);
    }
}
