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
 * @since         3.3.0
 */

namespace Cipherguard\PasswordGenerator\Test\TestCase;

use App\Test\Lib\AppIntegrationTestCase;

/**
 * @covers \Cipherguard\PasswordGenerator\PasswordGeneratorPlugin
 */
class PasswordGeneratorPluginTest extends AppIntegrationTestCase
{
    public function testPasswordGeneratorPlugin_Success_LegacyPasswordGeneratorSettingsRouteRedirect()
    {
        $this->get('/password-generator/settings.json');
        $this->assertRedirect('/password-policies/settings.json');
    }
}
