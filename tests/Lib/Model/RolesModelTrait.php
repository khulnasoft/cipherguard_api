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
namespace App\Test\Lib\Model;

trait RolesModelTrait
{
    /**
     * Asserts that an object has all the attributes a role should have.
     *
     * @param object $roles
     */
    protected function assertRoleAttributes($roles)
    {
        $attributes = ['id', 'name', 'description', 'created', 'modified'];
        $this->assertObjectHasAttributes($attributes, $roles);
    }
}
