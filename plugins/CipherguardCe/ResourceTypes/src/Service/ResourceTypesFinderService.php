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
 * @since         3.0.0
 */
namespace Cipherguard\ResourceTypes\Service;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cipherguard\ResourceTypes\Model\Entity\ResourceType;
use Cipherguard\ResourceTypes\Model\Table\ResourceTypesTable;

class ResourceTypesFinderService implements ResourceTypesFinderInterface
{
    /**
     * @var \Cipherguard\ResourceTypes\Model\Table\ResourceTypesTable
     */
    protected $resourceTypesTable;

    /**
     * Instantiate the service.
     */
    public function __construct()
    {
        /** @phpstan-ignore-next-line */
        $this->resourceTypesTable = TableRegistry::getTableLocator()->get('ResourceTypes');
    }

    /**
     * Returns resource types without TOTP resource types.
     *
     * @return \Cake\ORM\Query
     */
    public function find(): Query
    {
        return $this->resourceTypesTable
            ->find()
            ->where([
                'slug NOT IN' => [
                    ResourceType::SLUG_STANDALONE_TOTP,
                    ResourceType::SLUG_PASSWORD_DESCRIPTION_TOTP,
                ],
            ])
            ->formatResults(ResourceTypesTable::resultFormatter());
    }

    /**
     * Get a resource type by Id
     *
     * @param string $id uuid
     * @throws \Cake\Datasource\Exception\RecordNotFoundException if resource type is not present
     * @return array|\Cake\Datasource\EntityInterface
     */
    public function get(string $id)
    {
        return $this->find()
            ->where(['id' => $id])
            ->firstOrFail();
    }
}
