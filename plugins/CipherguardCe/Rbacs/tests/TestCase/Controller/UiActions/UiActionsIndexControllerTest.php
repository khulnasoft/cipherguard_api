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

namespace Cipherguard\Rbacs\Test\TestCase\Controller\UiActions;

use Cipherguard\Rbacs\Model\Entity\UiAction;
use Cipherguard\Rbacs\Service\UiActions\UiActionsInsertDefaultsService;
use Cipherguard\Rbacs\Test\Lib\RbacsIntegrationTestCase;

/**
 * Cipherguard\Rbacs\Controller\UiActions\UiActionsIndexController Test Case
 *
 * @uses \Cipherguard\Rbacs\Controller\UiActions\UiActionsIndexController
 */
class UiActionsIndexControllerTest extends RbacsIntegrationTestCase
{
    /**
     * Check complete list of UiActions is available to admin
     */
    public function testUiActionsIndexController_Success(): void
    {
        (new UiActionsInsertDefaultsService())->insertDefaultsIfNotExist();
        $this->logInAsAdmin();

        $this->getJson('/rbacs/uiactions.json');

        $this->assertSuccess();
        $response = $this->getResponseBodyAsArray();
        $this->assertNotEmpty($response);
        $uiAction = $response[0];
        $this->assertArrayHasAttributes(['id', 'name', 'allowed_control_functions'], $uiAction);
        $this->assertEqualsCanonicalizing(
            UiAction::CONTROL_FUNCTION_MAPPING[$uiAction['name']],
            $uiAction['allowed_control_functions']
        );
    }

    /**
     * Check complete list of UiActions is not available to guests
     */
    public function testUiActionsIndexController_Error_NotAuthenticated(): void
    {
        $this->getJson('/rbacs/uiactions.json');
        $this->assertAuthenticationError();
    }

    /**
     * Check complete list of UiActions is only available to admin
     */
    public function testUiActionsIndexController_Error_ForbiddenForUser(): void
    {
        $this->logInAsUser();
        $this->getJson('/rbacs/uiactions.json');
        $this->assertError(403);
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testUiActionsIndexController_Error_NotJson(): void
    {
        $this->logInAsAdmin();
        $this->get('/rbacs/uiactions');
        $this->assertResponseCode(404);
    }
}
