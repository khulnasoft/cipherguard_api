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

namespace App\Test\TestCase\Controller\Settings;

use App\Model\Validation\EmailValidationRule;
use App\Test\Lib\AppIntegrationTestCase;
use Cake\Core\Configure;

class SettingsIndexControllerTest extends AppIntegrationTestCase
{
    public function testSettingsIndexController_SuccessAsLU(): void
    {
        // Enable all plugins. This test is important as, it also tests the complete
        // integration of the plugins
        $plugins = array_keys(Configure::read('cipherguard.plugins'));
        foreach ($plugins as $plugin) {
            $this->enableFeaturePlugin(ucfirst($plugin));
        }
        $this->logInAsUser();
        $this->getJson('/settings.json');
        $this->assertSuccess();
        $this->assertGreaterThan(0, count((array)$this->_responseJsonBody));
        $this->assertGreaterThan(1, count((array)$this->_responseJsonBody->app));
        $this->assertTrue(isset($this->_responseJsonBody->app->version));
        $this->assertSame('en-UK', $this->_responseJsonBody->app->locale);
        $this->assertEquals(
            json_decode(json_encode(Configure::read('cipherguard.plugins.locale.options'))),
            $this->_responseJsonBody->cipherguard->plugins->locale->options
        );

        // Assert some default plugin visibility
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->export->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->accountRecoveryRequestHelp->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->healthcheck->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->disableUser->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->healthcheckUi->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->log->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->multiFactorAuthentication->enabled));
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->folders->enabled));
        foreach ($plugins as $plugin) {
            $this->assertTrue($this->_responseJsonBody->cipherguard->plugins->{$plugin}->enabled);
        }
    }

    public function testSettingsIndexController_SuccessAsAN(): void
    {
        $this->getJson('/settings.json');
        $this->assertSuccess();
        $this->assertGreaterThan(0, count((array)$this->_responseJsonBody));
        $this->assertFalse(isset($this->_responseJsonBody->app->version));
        $this->assertTrue(isset($this->_responseJsonBody->app->url));
        $this->assertSame('en-UK', $this->_responseJsonBody->app->locale);
        $this->assertEquals(
            json_decode(json_encode(Configure::read('cipherguard.plugins.locale.options'))),
            $this->_responseJsonBody->cipherguard->plugins->locale->options
        );

        // Assert LU only plugin is not visible
        $this->assertFalse(isset($this->_responseJsonBody->cipherguard->plugins->export->enabled));
        $this->assertFalse(isset($this->_responseJsonBody->cipherguard->plugins->disableUser->enabled));

        // Assert AN plugin is visible
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->accountRecoveryRequestHelp->enabled));
        $this->assertFalse(isset($this->_responseJsonBody->cipherguard->email));
    }

    public function testSettingsIndexController_SuccessAsAN_With_Email_Regex_Defined(): void
    {
        $regex = 'Foo';
        Configure::write(EmailValidationRule::REGEX_CHECK_KEY, $regex);
        $this->getJson('/settings.json');
        $this->assertSuccess();

        $this->assertSame($regex, $this->_responseJsonBody->cipherguard->email->validate->regex);
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testSettingsIndexController_Error_NotJson(): void
    {
        $this->logInAsUser();
        $this->get('/settings');
        $this->assertResponseCode(404);
    }
}
