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
 * @since         3.10.0
 */
namespace Cipherguard\SelfRegistration\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Validation\Validation;
use Cipherguard\SelfRegistration\Service\SelfRegistrationDeleteSettingsService;

class SelfRegistrationDeleteSettingsController extends AppController
{
    /**
     * Self Registration DELETE action
     *
     * @param string|null $id ID of the setting to delete
     * @return void
     */
    public function deleteSettings(?string $id): void
    {
        $this->User->assertIsAdmin();

        // Check request sanity
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The self registration setting id should be a valid UUID.'));
        }

        $service = new SelfRegistrationDeleteSettingsService();
        $service->deleteSettings($this->User->getAccessControl(), $id);
        $this->success(__('The operation was successful.'));
    }
}
