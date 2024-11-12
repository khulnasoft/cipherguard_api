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

namespace App\Test\TestCase\Controller\Roles;

use App\Test\Lib\AppIntegrationTestCase;

class RolesIndexControllerTest extends AppIntegrationTestCase
{
    public $fixtures = ['app.Base/Users', 'app.Base/Roles'];

    public function testRolesIndexController_Success(): void
    {
        $this->logInAsUser();
        $this->getJson('/roles.json');
        $this->assertSuccess();
        $this->assertGreaterThan(1, count($this->_responseJsonBody));
        $this->assertRoleAttributes($this->_responseJsonBody[0]);
    }

    public function testRolesIndexController_Error_NotAuthenticated(): void
    {
        $this->getJson('/roles.json');
        $this->assertAuthenticationError();
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testRolesIndexController_Error_NotJson(): void
    {
        $this->logInAsUser();
        $this->get('/roles');
        $this->assertResponseCode(404);
    }
}
