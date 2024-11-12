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
 * @since         2.13.0
 */

namespace Cipherguard\Folders\Service\GroupsUsers;

use App\Model\Entity\GroupsUser;
use App\Model\Table\PermissionsTable;
use App\Utility\UserAccessControl;
use Cake\ORM\TableRegistry;
use Cipherguard\Folders\Model\Dto\FolderRelationDto;
use Cipherguard\Folders\Model\Entity\FoldersRelation;
use Cipherguard\Folders\Service\FoldersRelations\FoldersRelationsAddItemsToUserTreeService;

class HandleGroupUserAddedService
{
    /**
     * @var \App\Model\Table\PermissionsTable
     */
    private $permissionsTable;

    /**
     * @var \Cipherguard\Folders\Model\Table\FoldersRelationsTable
     */
    private $foldersRelationsTable;

    /**
     * @var \Cipherguard\Folders\Service\FoldersRelations\FoldersRelationsAddItemsToUserTreeService
     */
    private $foldersRelationsAddItemsFromUserTree;

    /**
     * Instantiate the service.
     */
    public function __construct()
    {
        $this->permissionsTable = TableRegistry::getTableLocator()->get('Permissions');
        $this->foldersRelationsTable = TableRegistry::getTableLocator()->get('Cipherguard/Folders.FoldersRelations');
        $this->foldersRelationsAddItemsFromUserTree = new FoldersRelationsAddItemsToUserTreeService();
    }

    /**
     * Handle a group user addition.
     *
     * @param \App\Utility\UserAccessControl $uac The current user
     * @param \App\Model\Entity\GroupsUser $groupUser The added group user.
     * @return void
     * @throws \Exception If something unexpected occurred
     */
    public function handle(UserAccessControl $uac, GroupsUser $groupUser)
    {
        $items = [];

        $missingFoldersRelationsFoldersIds = $this->getMissingFoldersRelationsFoldersIds($groupUser);
        foreach ($missingFoldersRelationsFoldersIds as $missingFolderRelationFolderId) {
            $items[] = new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_FOLDER, $missingFolderRelationFolderId);
        }

        $missingFoldersRelationsResourcesIds = $this->getMissingFoldersRelationsResourcesIds($groupUser);
        foreach ($missingFoldersRelationsResourcesIds as $missingFolderRelationResourceId) {
            $items[] = new FolderRelationDto(FoldersRelation::FOREIGN_MODEL_RESOURCE, $missingFolderRelationResourceId);
        }

        $this->foldersRelationsAddItemsFromUserTree->addItemsToUserTree($uac, $groupUser->user_id, $items);
    }

    /**
     * Get the folders ids which are shared with the group but the user does not have a folder relation for it yet.
     *
     * @param \App\Model\Entity\GroupsUser $groupUser The group user to add.
     * @return array
     */
    private function getMissingFoldersRelationsFoldersIds(GroupsUser $groupUser): array
    {
        $groupUserFoldersRelationsFoldersIdsQuery = $this->foldersRelationsTable->find()
            ->where([
                'user_id' => $groupUser->user_id,
                'foreign_model' => FoldersRelation::FOREIGN_MODEL_FOLDER,
            ])->select('foreign_id');

        return $this->permissionsTable->findAllByAro(PermissionsTable::FOLDER_ACO, $groupUser->group_id)
            ->select('aco_foreign_key')
            ->where(['aco_foreign_key NOT IN' => $groupUserFoldersRelationsFoldersIdsQuery])
            ->all()
            ->extract('aco_foreign_key')
            ->toArray();
    }

    /**
     * Get the resources ids which are shared with the group but the user does not have a folder relation for it yet.
     *
     * @param \App\Model\Entity\GroupsUser $groupUser The group user to add.
     * @return array
     */
    private function getMissingFoldersRelationsResourcesIds(GroupsUser $groupUser): array
    {
        $groupUserFoldersRelationsResourcesIdsQuery = $this->foldersRelationsTable->find()
            ->where([
                'user_id' => $groupUser->user_id,
                'foreign_model' => FoldersRelation::FOREIGN_MODEL_RESOURCE,
            ])->select('foreign_id');

        return $this->permissionsTable->findAllByAro(PermissionsTable::RESOURCE_ACO, $groupUser->group_id)
            ->select('aco_foreign_key')
            ->where(['aco_foreign_key NOT IN' => $groupUserFoldersRelationsResourcesIdsQuery])
            ->all()
            ->extract('aco_foreign_key')
            ->toArray();
    }
}
