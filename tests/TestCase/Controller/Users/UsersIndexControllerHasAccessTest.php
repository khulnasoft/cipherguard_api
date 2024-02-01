<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         3.6.0
 */

namespace App\Test\TestCase\Controller\Users;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\UuidFactory;

class UsersIndexControllerHasAccessTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        RoleFactory::make()->guest()->persist();
    }

    public function testUsersIndexController_Success_FilterByHasAccessSuccess(): void
    {
        $users = UserFactory::make(2)->user()->persist();
        $resourceFactory = ResourceFactory::make();
        $resource = $resourceFactory->withCreatorAndPermission($users[0])->persist();
        $resourceFactory->persist();

        $this->logInAs($users[0]);
        $this->getJson('/users.json?filter[has-access]=' . $resource->id);
        $this->assertResponseOk();
        $this->assertCount(1, $this->_responseJsonBody);
        $this->assertSame($users[0]->id, $this->_responseJsonBody[0]->id);
    }

    public function testUsersIndexController_Error_FilterByHasAccess_NotAllowed(): void
    {
        $users = UserFactory::make(2)->user()->persist();
        $resourceFactory = ResourceFactory::make();
        $resource = $resourceFactory->withCreatorAndPermission($users[0])->persist();
        $resourceFactory->persist();

        $this->logInAs($users[1]);
        $this->getJson('/users.json?filter[has-access]=' . $resource->id);
        $this->assertError(403, 'This operation is not allowed for this user.');
    }

    public function testUsersIndexController_Error_FilterByHasAccess_BadRequest(): void
    {
        $users = UserFactory::make(2)->user()->persist();
        $resourceFactory = ResourceFactory::make();
        $resource = $resourceFactory->withCreatorAndPermission($users[0])->persist();
        $resourceFactory->persist();

        $this->logInAs($users[1]);
        $filter = 'filter[has-access][]=' . $resource->id . '&filter[has-access][]=' . UuidFactory::uuid();
        $this->getJson('/users.json?' . $filter);
        $this->assertError(400);
    }
}
