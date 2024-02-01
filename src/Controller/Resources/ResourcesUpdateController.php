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
 * @since         2.0.0
 */

namespace App\Controller\Resources;

use App\Controller\AppController;
use App\Service\Resources\ResourcesUpdateService;
use Cake\Http\Exception\BadRequestException;
use Cake\Validation\Validation;

/**
 * ResourcesUpdateController Class
 */
class ResourcesUpdateController extends AppController
{
    /**
     * Resource Update action
     *
     * @param string $id The identifier of the resource to update.
     * @throws \Cake\Http\Exception\NotFoundException If the resource is soft deleted.
     * @throws \Cake\Http\Exception\NotFoundException If the user does not have access to the resource.
     * @throws \Cake\Http\Exception\BadRequestException If the resource id is not a valid uuid.
     * @throws \Exception If an unexpected error occurred
     * @throws \Cake\Http\Exception\NotFoundException If the resource does not exist.
     * @return void
     */
    public function update(string $id): void
    {
        $this->assertJson();

        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The resource identifier should be a valid UUID.'));
        }

        $uac = $this->User->getAccessControl();
        $resourceUpdateService = new ResourcesUpdateService();
        $data = $this->request->getData();
        $resource = $resourceUpdateService->update($uac, $id, $data);

        // Retrieve the updated resource.
        $options = [
            'contain' => [
                'creator' => true, 'favorite' => true, 'modifier' => true, 'secret' => true, 'permission' => true,
            ],
        ];
        /** @var \App\Model\Table\ResourcesTable $resourcesTable */
        $resourcesTable = $this->fetchTable('Resources');
        $output = $resourcesTable->findView($this->User->id(), $resource->id, $options)->first();

        $this->success(__('The resource has been updated successfully.'), $output);
    }
}
