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
 * @since         2.13.0
 */

namespace Cipherguard\Folders\Service\Resources;

use App\Model\Entity\Resource;
use Cake\ORM\TableRegistry;

class ResourcesAfterSoftDeleteService
{
    /**
     * @param \App\Model\Entity\Resource $resource The soft deleted resource.
     * @return void
     * @throws \Exception
     */
    public function afterSoftDelete(Resource $resource)
    {
        TableRegistry::getTableLocator()
            ->get('Cipherguard/Folders.FoldersRelations')
            ->deleteAll(['FoldersRelations.foreign_id' => $resource->id]);
    }
}
