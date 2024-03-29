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
 * @since         2.0.0
 */
namespace App\Test\TestCase\Controller\Groups;

use App\Model\Entity\Permission;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\GroupsModelTrait;
use App\Utility\UuidFactory;
use Cake\ORM\TableRegistry;

class GroupsDeleteControllerTest extends AppIntegrationTestCase
{
    use GroupsModelTrait;

    public $Groups;
    public $Permissions;

    public $fixtures = [
        'app.Base/Users', 'app.Base/Groups', 'app.Base/Profiles', 'app.Base/Gpgkeys', 'app.Base/Roles',
        'app.Base/Resources', 'app.Base/Favorites', 'app.Base/Secrets',
        'app.Alt0/GroupsUsers', 'app.Alt0/Permissions',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->Groups = TableRegistry::getTableLocator()->get('Groups');
        $this->Permissions = TableRegistry::getTableLocator()->get('Permissions');
    }

    public function testGroupsDeleteDryRunSuccess(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.freelancer');
        $this->deleteJson('/groups/' . $groupId . '/dry-run.json');
        $this->assertSuccess();
        $group = $this->Groups->get($groupId);
        $this->assertFalse($group->deleted);
    }

    public function testGroupsDeleteDryRunError_MissingCsrfToken(): void
    {
        $this->disableCsrfToken();
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.freelancer');
        $this->delete('/groups/' . $groupId . '/dry-run.json');
        $this->assertResponseCode(403);
    }

    public function testGroupsDeleteDryRunError(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.creative');
        $this->deleteJson('/groups/' . $groupId . '/dry-run.json');
        $this->assertError(400);
        $this->assertStringContainsString(
            'transfer the ownership',
            $this->_responseJsonHeader->message
        );
    }

    public function testGroupsDeleteNotLoggedInError(): void
    {
        $groupId = UuidFactory::uuid('group.id.freelancer');
        $this->deleteJson('/groups/' . $groupId . '.json');
        $this->assertAuthenticationError();
    }

    public function testGroupsDeleteNotAdminError(): void
    {
        $this->authenticateAs('ada');
        $groupId = UuidFactory::uuid('group.id.freelancer');
        $this->deleteJson('/groups/' . $groupId . '.json');
        $this->assertForbiddenError('You are not authorized to access that location.');
    }

    public function testGroupsDeleteInvalidGroupError(): void
    {
        $this->authenticateAs('admin');
        $bogusId = '0';
        $this->deleteJson('/groups/' . $bogusId . '.json');
        $this->assertError(400, 'The group identifier should be a valid UUID.');

        $this->authenticateAs('admin');
        $bogusId = 'true';
        $this->deleteJson('/groups/' . $bogusId . '.json');
        $this->assertError(400, 'The group identifier should be a valid UUID.');

        $this->authenticateAs('admin');
        $bogusId = 'null';
        $this->deleteJson('/groups/' . $bogusId . '.json');
        $this->assertError(400, 'The group identifier should be a valid UUID.');

        $this->authenticateAs('admin');
        $bogusId = '🔥';
        $this->deleteJson('/groups/' . $bogusId . '.json');
        $this->assertError(400, 'The group identifier should be a valid UUID.');
    }

    public function testGroupsDeleteGroupDoesNotExistError(): void
    {
        $this->authenticateAs('admin');
        $bogusId = UuidFactory::uuid('group.id.bogus');
        $this->deleteJson('/groups/' . $bogusId . '.json');
        $this->assertError(404, 'The group does not exist or has been already deleted.');
    }

    public function testGroupsDeleteGroupAlreadyDeletedError(): void
    {
        // Delete the group twice
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.freelancer');
        $this->deleteJson('/groups/' . $groupId . '.json');
        $this->deleteJson('/groups/' . $groupId . '.json');
        $this->assertError(404, 'The group does not exist or has been already deleted.');
    }

    public function testGroupsDeleteSuccess_NoOwnerNoResourcesSharedNoGroupsMember_DelGroupCase0(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.procurement');
        $this->deleteJson("/groups/$groupId.json");
        $this->assertSuccess();
        $this->assertGroupIsSoftDeleted($groupId);
    }

    public function testGroupsDeleteSucces_SharedResourceWithMe_DelGroupCase1(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.quality_assurance');
        $this->deleteJson("/groups/$groupId.json");
        $this->assertSuccess();
        $this->assertGroupIsSoftDeleted($groupId);
    }

    public function testGroupsDeleteSucces_SoleOwnerNotSharedResource_DelGroupCase2(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.resource_planning');
        $this->deleteJson("/groups/$groupId.json");
        $this->assertSuccess();
        $this->assertGroupIsSoftDeleted($groupId);
    }

    private function applyPermissionChangesForCase3($resourceId, $groupId, $userId): void
    {
        $permission = $this->Permissions->find()->select()->where([
            'aro_foreign_key' => $userId,
            'aco_foreign_key' => $resourceId,
        ])->first();
        $permission->type = Permission::READ;
        $this->Permissions->save($permission);
        $permission = $this->Permissions->find()->select()->where([
            'aro_foreign_key' => $groupId,
            'aco_foreign_key' => $resourceId,
        ])->first();
        $permission->type = Permission::OWNER;
        $this->Permissions->save($permission);
    }

    public function testGroupsDeleteError_SoleOwnerSharedResource_DelGroupCase3(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.quality_assurance');
        $resourceId = UuidFactory::uuid('resource.id.nodejs');
        $userId = UuidFactory::uuid('user.id.marlyn');

        // CONTEXTUAL TEST CHANGES Make the group sole owner of the resource
        $this->applyPermissionChangesForCase3($resourceId, $groupId, $userId);

        $this->deleteJson("/groups/$groupId.json");
        $this->assertError(400);
        $this->assertGroupIsNotSoftDeleted($groupId);
        $this->assertStringContainsString('transfer the ownership', $this->_responseJsonHeader->message);

        $errors = $this->_responseJsonBody->errors;
        $this->assertEquals(1, count($errors->resources->sole_owner));

        $resource = $errors->resources->sole_owner[0];
        $this->assertResourceAttributes($resource);
        $this->assertEquals($resource->id, $resourceId);
    }

    public function testGroupsDeleteError_TransferOwnersOfAnotherResource_SoleOwnerSharedResource_DelGroupCase3(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.quality_assurance');
        $resourceId = UuidFactory::uuid('resource.id.nodejs');
        $resourceSId = UuidFactory::uuid('resource.id.selenium');
        $userId = UuidFactory::uuid('user.id.marlyn');

        // CONTEXTUAL TEST CHANGES Make the group sole owner of the resource
        $this->applyPermissionChangesForCase3($resourceId, $groupId, $userId);

        $transfer['owners'][] = ['id' => UuidFactory::uuid('permission.id.selenium-margaret'), 'aco_foreign_key' => $resourceSId];
        $this->deleteJson("/groups/$groupId.json", ['transfer' => $transfer]);
        $this->assertError(400, 'The transfer is not authorized');
        $this->assertGroupIsNotSoftDeleted($groupId);
    }

    public function testGroupsDeleteError_TransferOwnersBadGroupUserId_SoleOwnerSharedResource_DelGroupCase3(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.quality_assurance');
        $resourceId = UuidFactory::uuid('resource.id.nodejs');
        $userId = UuidFactory::uuid('user.id.marlyn');

        // CONTEXTUAL TEST CHANGES Make the group sole owner of the resource
        $this->applyPermissionChangesForCase3($resourceId, $groupId, $userId);

        $transfer['owners'][] = ['id' => 'invalid-uuid', 'aco_foreign_key' => $resourceId];
        $this->deleteJson("/groups/$groupId.json", ['transfer' => $transfer]);
        $this->assertError(400, 'The permissions identifiers must be valid UUID.');
        $this->assertGroupIsNotSoftDeleted($groupId);
    }

    public function testGroupsDeleteSuccess_SoleOwnerSharedResource_DelGroupCase3(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.quality_assurance');
        $resourceId = UuidFactory::uuid('resource.id.nodejs');
        $userMId = UuidFactory::uuid('user.id.marlyn');

        // CONTEXTUAL TEST CHANGES Make the group sole owner of the resource
        $permission = $this->Permissions->find()->select()->where([
            'aro_foreign_key' => $userMId,
            'aco_foreign_key' => $resourceId,
        ])->first();
        $permission->type = Permission::READ;
        $this->Permissions->save($permission);
        $permission = $this->Permissions->find()->select()->where([
            'aro_foreign_key' => $groupId,
            'aco_foreign_key' => $resourceId,
        ])->first();
        $permission->type = Permission::OWNER;
        $this->Permissions->save($permission);

        $transfer['owners'][] = ['id' => UuidFactory::uuid('permission.id.nodejs-marlyn'), 'aco_foreign_key' => $resourceId];
        $this->deleteJson("/groups/$groupId.json", ['transfer' => $transfer]);
        $this->assertSuccess();
        $this->assertGroupIsSoftDeleted($groupId);
        $this->assertPermission($resourceId, $userMId, Permission::OWNER);
    }

    public function testGroupsSoftDeleteSuccess_OwnerAlongWithAnotherUser_DelGroupCase4(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.management');
        $this->deleteJson("/groups/$groupId.json");
        $this->assertSuccess();
        $this->assertGroupIsSoftDeleted($groupId);
    }

    public function testGroupsDeleteAsGroupOwnerSuccess(): void
    {
        $this->authenticateAs('edith');
        $groupId = UuidFactory::uuid('group.id.freelancer');
        $this->deleteJson('/groups/' . $groupId . '.json');
        $this->assertSuccess();
        $this->assertGroupIsSoftDeleted($groupId);
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testGroupsDeleteController_Error_NotJson(): void
    {
        $this->authenticateAs('admin');
        $groupId = UuidFactory::uuid('group.id.procurement');
        $this->delete("/groups/$groupId");
        $this->assertResponseCode(404);
    }
}
