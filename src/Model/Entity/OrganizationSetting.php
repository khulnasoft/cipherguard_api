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

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsersSetting Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $property
 * @property string $value
 *
 * @property \App\Model\Entity\User $user
 * @property string $property_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property string $created_by
 * @property string $modified_by
 */
class OrganizationSetting extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'property_id' => true,
        'property' => true,
        'value' => true,
        'created_by' => true,
        'modified_by' => true,
    ];

    public const UUID_NAMESPACE = 'organization.settings.property.id.';
}
