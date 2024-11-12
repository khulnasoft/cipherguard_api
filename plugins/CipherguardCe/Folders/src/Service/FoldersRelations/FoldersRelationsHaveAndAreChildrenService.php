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

namespace Cipherguard\Folders\Service\FoldersRelations;

use Cake\ORM\TableRegistry;
use Cipherguard\Folders\Model\Entity\FoldersRelation;
use Cipherguard\Folders\Model\Table\FoldersRelationsTable;

class FoldersRelationsHaveAndAreChildrenService
{
    /**
     * @var \Cipherguard\Folders\Model\Table\FoldersRelationsTable
     */
    private FoldersRelationsTable $foldersRelationsTable;

    /**
     * Instantiate the service.
     */
    public function __construct()
    {
        $this->foldersRelationsTable = TableRegistry::getTableLocator()->get('Cipherguard/Folders.FoldersRelations');
    }

    /**
     * Check if the given folders have children and are children.
     *
     * @param array<string> $foldersIds The list of folders identifiers to check for.
     * @param string|null $userId The target user id to check for. If not provided, check for all users.
     * @return bool
     */
    public function haveAndAreChildren(array $foldersIds, ?string $userId = null): bool
    {
        if (empty($foldersIds)) {
            return false;
        }

        $parentsFoldersRelationsQuery = $this->foldersRelationsTable->find()
            ->select('folder_parent_id')
            ->where([
                'foreign_model' => FoldersRelation::FOREIGN_MODEL_FOLDER,
                'folder_parent_id IN' => $foldersIds,
            ]);

        $childrenFoldersRelationsAlsoParentsQuery = $this->foldersRelationsTable->find()
            ->where([
                'foreign_model' => FoldersRelation::FOREIGN_MODEL_FOLDER,
                'folder_parent_id IS NOT NULL',
                'foreign_id IN' => $parentsFoldersRelationsQuery,
            ])->limit(1)
            ->disableHydration();

        if (!is_null($userId)) {
            $parentsFoldersRelationsQuery->andWhere(['user_id' => $userId]);
            $childrenFoldersRelationsAlsoParentsQuery->andWhere(['user_id' => $userId]);
        }

        return count($childrenFoldersRelationsAlsoParentsQuery->all()->toArray()) !== 0;
    }
}
