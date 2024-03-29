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
namespace Cipherguard\WebInstaller\Test\TestCase\Controller;

use App\Utility\Healthchecks;
use Cake\Core\Configure;
use Cipherguard\WebInstaller\Test\Lib\WebInstallerIntegrationTestCase;

class SystemCheckControllerTest extends WebInstallerIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->skipTestIfNotWebInstallerFriendly();
        $this->mockCipherguardIsNotconfigured();
        $this->initWebInstallerSession();
    }

    /**
     * note: creates an issue with healthcheck on a webserverless environment.
     */
    public function testWebInstallerSystemCheckViewSuccess()
    {
        $this->get('/install/system_check');

        $data = $this->_getBodyAsString();

        $this->assertResponseOk();
        $minPhpVersion = Configure::read(Healthchecks::PHP_MIN_VERSION_CONFIG);
        $nextMinPhpVersion = Configure::read(Healthchecks::PHP_NEXT_MIN_VERSION_CONFIG);
        if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
            $this->assertStringContainsString('PHP version is too low', $data);
        } elseif (
            version_compare(PHP_VERSION, $minPhpVersion, '>=')
            && version_compare(PHP_VERSION, $nextMinPhpVersion, '<=')
        ) {
            // Warning is shown to user
            $this->assertStringContainsString(
                __(
                    'PHP version less than {0} will soon be not supported by cipherguard, so consider upgrading your operating system or PHP environment', // phpcs:ignore
                    $nextMinPhpVersion
                ),
                $data
            );
            // User can continue with the installation
            $this->assertStringContainsString('Nice one! Your environment is ready for cipherguard.', $data);
            $this->assertStringContainsString('GPG is configured correctly.', $data);
            $this->assertStringContainsString('Start configuration', $data);
        } else {
            $this->assertStringContainsString('. Database', $data);
            $this->assertStringContainsString('Nice one! Your environment is ready for cipherguard.', $data);
            $this->assertStringContainsString('Environment is configured correctly.', $data);
            $this->assertStringContainsString('GPG is configured correctly.', $data);
            $this->assertStringContainsString('Start configuration', $data);
        }
    }

    /**
     * note: creates an issue with healthcheck on a webserverless environment.
     */
    public function testWebInstallerSystemCheckViewSuccess_LicensePluginEnabled()
    {
        $this->get('/install/system_check');

        $data = $this->_getBodyAsString();
        $this->assertResponseOk();
        $minPhpVersion = Configure::read(Healthchecks::PHP_MIN_VERSION_CONFIG);
        $nextMinPhpVersion = Configure::read(Healthchecks::PHP_NEXT_MIN_VERSION_CONFIG);
        if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
            $this->assertStringContainsString('PHP version is too low', $data);
        } elseif (version_compare(PHP_VERSION, $nextMinPhpVersion, '<=')) {
            $this->assertStringContainsString(
                __(
                    'PHP version less than {0} will soon be not supported by cipherguard, so consider upgrading your operating system or PHP environment', // phpcs:ignore
                    $nextMinPhpVersion
                ),
                $data
            );
        } else {
            $this->assertStringContainsString('Start configuration', $data);
        }
    }
}
