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

namespace App\Test\TestCase\Controller\Notifications;

use App\Model\Entity\Permission;
use App\Test\Lib\Model\EmailQueueTrait;
use App\Test\TestCase\Controller\Share\ShareControllerTest;
use App\Utility\UuidFactory;
use Cipherguard\EmailNotificationSettings\Test\Lib\EmailNotificationSettingsTestTrait;

class ShareNotificationTest extends ShareControllerTest
{
    use EmailQueueTrait;
    use EmailNotificationSettingsTestTrait;

    public $fixtures = [
        'app.Base/Users', 'app.Base/Gpgkeys', 'app.Base/Profiles', 'app.Base/Roles',
        'app.Base/Groups', 'app.Base/GroupsUsers', 'app.Base/Resources', 'app.Base/Permissions',
        'app.Base/Secrets', 'app.Base/Favorites',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->loadNotificationSettings();
    }

    public function tearDown(): void
    {
        $this->unloadNotificationSettings();
        parent::tearDown();
    }

    public function testShareNotificationSuccess(): void
    {
        $this->setEmailNotificationSettings([
            'show.description' => true,
            'show.username' => true,
            'show.uri' => true,
            'show.secret' => true,
        ]);

        // Define actors of this tests
        $resourceId = UuidFactory::uuid('resource.id.cakephp');
        // Users
        $userAId = UuidFactory::uuid('user.id.ada');
        $userBId = UuidFactory::uuid('user.id.betty');
        $userEId = UuidFactory::uuid('user.id.edith');
        $userFId = UuidFactory::uuid('user.id.frances');
        // Groups
        $groupBId = UuidFactory::uuid('group.id.board');
        $groupFId = UuidFactory::uuid('group.id.freelancer');
        $groupAId = UuidFactory::uuid('group.id.accounting');

        // Expected results.
        $expectedAddedUsersIds = [];
        $expectedRemovedUsersIds = [];

        // Build the changes.
        $data = ['permissions' => []];

        // Users permissions changes.
        // Change the permission of the user Ada to read (no users are expected to be added or removed).
        $data['permissions'][] = ['id' => UuidFactory::uuid("permission.id.$resourceId-$userAId"), 'type' => Permission::READ];
        // Delete the permission of the user Betty.
        $data['permissions'][] = ['id' => UuidFactory::uuid("permission.id.$resourceId-$userBId"), 'delete' => true];
        $expectedRemovedUsersIds[] = $userBId;
        // Add an owner permission for the user Edith
        $data['permissions'][] = ['aro' => 'User', 'aro_foreign_key' => $userEId, 'type' => Permission::OWNER];
        $data['secrets'][] = ['user_id' => $userEId, 'data' => $this->getValidSecret()];
        $expectedAddedUsersIds[] = $userEId;

        // Groups permissions changes.
        // Change the permission of the group Board (no users are expected to be added or removed).
        $data['permissions'][] = ['id' => UuidFactory::uuid("permission.id.$resourceId-$groupBId"), 'type' => Permission::OWNER];
        // Delete the permission of the group Freelancer.
        $data['permissions'][] = ['id' => UuidFactory::uuid("permission.id.$resourceId-$groupFId"), 'delete' => true];
        // Add a read permission for the group Accounting.
        $data['permissions'][] = ['aro' => 'Group', 'aro_foreign_key' => $groupAId, 'type' => Permission::READ];
        $data['secrets'][] = ['user_id' => $userFId, 'data' => $this->getValidSecret()];

        $this->authenticateAs('ada');
        $this->putJson("/share/resource/$resourceId.json", $data);
        $this->assertSuccess();

        // check email notification
        $this->assertEmailInBatchContains('shared a password with you', 'edith@cipherguard.github.io');
        $this->assertEmailInBatchContains('Name: cakephp', 'edith@cipherguard.github.io');
        $this->assertEmailInBatchContains('Username: cake', 'edith@cipherguard.github.io');
        $this->assertEmailInBatchContains('The rapid and tasty php development framework', 'edith@cipherguard.github.io');
        $this->assertEmailInBatchContains('URL: cakephp.org', 'edith@cipherguard.github.io');
        $this->assertEmailInBatchContains('BEGIN PGP MESSAGE', 'edith@cipherguard.github.io');

        $this->assertEmailInBatchContains('shared a password with you', 'frances@cipherguard.github.io');
    }
}
