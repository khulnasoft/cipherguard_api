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

use App\Utility\UuidFactory;

trait FavoritesModelTrait
{
    /**
     * Get a dummy favorite with test data.
     * The comment returned passes a default validation.
     *
     * @param array|null $data Custom data that will be merged with the default content.
     * @return array data
     */
    public static function getDummyFavorite(?array $data = [])
    {
        $entityContent = [
            'user_id' => UuidFactory::uuid('user.id.dame'),
            'foreign_key' => UuidFactory::uuid('resource.id.bower'),
            'foreign_model' => 'Resource',
        ];
        $entityContent = array_merge($entityContent, $data);

        return $entityContent;
    }

    /**
     * Asserts that an object has all the attributes a favorite should have.
     *
     * @param object $favorite
     */
    public function assertFavoriteAttributes($favorite)
    {
        $attributes = ['id', 'user_id', 'foreign_key', 'foreign_model', 'created'];
        $this->assertObjectHasAttributes($attributes, $favorite);
    }
}
