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
 * @since         4.1.0
 */

namespace Cipherguard\Rbacs\Test\TestCase\Controller\Rbacs;

use Cipherguard\Rbacs\Test\Lib\RbacsIntegrationTestCase;

/**
 * Cipherguard\Rbacs\Controller\Rbacs\RbacsCreateController Test Case
 *
 * @uses \Cipherguard\Rbacs\Controller\Rbacs\RbacsIndexController
 */
class RbacsIndexControllerTest extends RbacsIntegrationTestCase
{
    /**
     * Check complete list of Rbacs is available to admin
     */
    public function testRbacsIndexController_Success(): void
    {
        $this->logInAsAdmin();
        $this->getJson('/rbacs.json');
        $this->assertSuccess();
    }

    /**
     * Check complete list of Rbacs is not available to guests
     */
    public function testRbacsIndexController_Error_NotAuthenticated(): void
    {
        $this->getJson('/rbacs.json');
        $this->assertAuthenticationError();
    }

    /**
     * Check complete list of Rbacs is only available to admin
     */
    public function testRbacsIndexController_Error_ForbiddenForUser(): void
    {
        $this->logInAsUser();
        $this->getJson('/rbacs.json');
        $this->assertError(403);
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testRbacsIndexController_Error_NotJson(): void
    {
        $this->logInAsAdmin();
        $this->get('/rbacs');
        $this->assertResponseCode(404);
    }
}
