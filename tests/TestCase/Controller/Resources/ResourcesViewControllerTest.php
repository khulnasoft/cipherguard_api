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

namespace App\Test\TestCase\Controller\Resources;

use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\FavoritesModelTrait;
use App\Test\Lib\Model\GroupsModelTrait;
use App\Utility\UuidFactory;
use Cake\ORM\TableRegistry;
use Cipherguard\Folders\FoldersPlugin;

class ResourcesViewControllerTest extends AppIntegrationTestCase
{
    use FavoritesModelTrait;
    use GroupsModelTrait;

    public $fixtures = [
        'app.Base/Users', 'app.Base/Profiles', 'app.Base/Roles', 'app.Base/Groups', 'app.Base/GroupsUsers', 'app.Base/Resources',
        'app.Base/Secrets', 'app.Base/Favorites', 'app.Base/Permissions',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(FoldersPlugin::class);
    }

    public function testResourcesViewController_Success(): void
    {
        $this->authenticateAs('dame');
        $resourceId = UuidFactory::uuid('resource.id.apache');
        $this->getJson("/resources/$resourceId.json");
        $this->assertSuccess();
        $this->assertNotNull($this->_responseJsonBody);

        // Expected fields.
        $this->assertResourceAttributes($this->_responseJsonBody);
        // Not expected fields.
        $this->assertObjectNotHasAttribute('secrets', $this->_responseJsonBody);
    }

    public function testResourcesViewController_SuccessWithContain(): void
    {
        $this->authenticateAs('ada');
        $urlParameter = 'contain[creator]=1&contain[favorite]=1&contain[modifier]=1&contain[secret]=1';
        $urlParameter .= '&contain[permission]=1&contain[permissions]=1';
        $urlParameter .= '&contain[permissions.user.profile]=1&contain[permissions.group]=1';
        $resourceId = UuidFactory::uuid('resource.id.git');
        $this->getJson("/resources/$resourceId.json?$urlParameter&api-version=2");
        $this->assertSuccess();

        // Expected fields.
        $this->assertResourceAttributes($this->_responseJsonBody);
        // Contain creator.
        $this->assertObjectHasAttribute('creator', $this->_responseJsonBody);
        $this->assertUserAttributes($this->_responseJsonBody->creator);

        // Contain modifier.
        $this->assertObjectHasAttribute('modifier', $this->_responseJsonBody);
        $this->assertUserAttributes($this->_responseJsonBody->modifier);

        // Contain permission.
        $this->assertObjectHasAttribute('permission', $this->_responseJsonBody);
        $this->assertPermissionAttributes($this->_responseJsonBody->permission);

        // Contain permissions.
        $this->assertObjectHasAttribute('permissions', $this->_responseJsonBody);
        $this->assertPermissionAttributes($this->_responseJsonBody->permissions[0]);

        // Contain permissions.user.
        $this->assertObjectHasAttribute('permissions', $this->_responseJsonBody);
        foreach ($this->_responseJsonBody->permissions as $permission) {
            if ($permission->aro === 'User') {
                $this->assertUserAttributes($permission->user);
                $this->assertProfileAttributes($permission->user->profile);
            } else {
                $this->assertGroupAttributes($permission->group);
            }
        }

        // Contain secret.
        $this->assertObjectHasAttribute('secrets', $this->_responseJsonBody);
        $this->assertCount(1, $this->_responseJsonBody->secrets);
        $this->assertSecretAttributes($this->_responseJsonBody->secrets[0]);

        // Apache
        $resourceId = UuidFactory::uuid('resource.id.apache');
        $this->getJson("/resources/$resourceId.json?$urlParameter&api-version=2");
        $this->assertSuccess();

        // Contain favorite.
        $this->assertObjectHasAttribute('favorite', $this->_responseJsonBody);

        // A resource marked as favorite contains the favorite data.
        $this->assertObjectHasAttribute('favorite', $this->_responseJsonBody);
        $this->assertFavoriteAttributes($this->_responseJsonBody->favorite);
    }

    public function testResourcesViewController_Error_NotAuthenticated(): void
    {
        $resourceId = UuidFactory::uuid('resource.id.bower');
        $this->getJson("/resources/$resourceId.json");
        $this->assertAuthenticationError();
    }

    public function testResourcesViewController_Error_NotValidId(): void
    {
        $this->authenticateAs('dame');
        $resourceId = 'invalid-id';
        $this->getJson("/resources/$resourceId.json");
        $this->assertError(400, 'The resource identifier should be a valid UUID.');
    }

    public function testResourcesViewController_Error_NotFound(): void
    {
        $this->authenticateAs('dame');
        $resourceId = UuidFactory::uuid('not-found');
        $this->getJson("/resources/$resourceId.json");
        $this->assertError(404, 'The resource does not exist.');
    }

    public function testResourcesViewController_Error_SoftDeletedResource(): void
    {
        $this->authenticateAs('dame');
        $resourceId = UuidFactory::uuid('resource.id.jquery');
        $this->getJson("/resources/$resourceId.json");
        $this->assertError(404, 'The resource does not exist.');
    }

    public function testResourcesViewController_Error_ResourceAccessDenied(): void
    {
        $resourceId = UuidFactory::uuid('resource.id.canjs');

        // Check that the resource exists.
        $Resources = TableRegistry::getTableLocator()->get('Resources');
        $resource = $Resources->get($resourceId);
        $this->assertNotNull($resource);

        // Check that the user cannot access the resource
        $this->authenticateAs('dame');
        $this->getJson("/resources/$resourceId.json");
        $this->assertError(404, 'The resource does not exist.');
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testResourcesViewController_Error_NotJson(): void
    {
        $this->authenticateAs('dame');
        $resourceId = UuidFactory::uuid('resource.id.apache');
        $this->get("/resources/$resourceId");
        $this->assertResponseCode(404);
    }
}
