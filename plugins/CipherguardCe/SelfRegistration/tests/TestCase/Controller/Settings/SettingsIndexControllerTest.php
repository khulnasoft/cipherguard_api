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
 * @since         3.10.0
 */

namespace Cipherguard\SelfRegistration\Test\TestCase\Controller\Settings;

use App\Test\Lib\AppIntegrationTestCase;
use Cipherguard\SelfRegistration\SelfRegistrationPlugin;

class SettingsIndexControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(SelfRegistrationPlugin::class);
    }

    public function testSettingsIndexController_publicPluginSettings()
    {
        $url = '/settings.json?api-version=2';
        $this->getJson($url);
        $this->assertSuccess();
        $this->assertTrue(isset($this->_responseJsonBody->cipherguard->plugins->selfRegistration->enabled));
    }
}
