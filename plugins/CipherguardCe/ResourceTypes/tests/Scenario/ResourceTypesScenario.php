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
 * @since         4.0.0
 */
namespace Cipherguard\ResourceTypes\Test\Scenario;

use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use Cipherguard\ResourceTypes\Model\Definition\SlugDefinition;
use Cipherguard\ResourceTypes\Model\Entity\ResourceType;
use Cipherguard\ResourceTypes\Test\Factory\ResourceTypeFactory;

class ResourceTypesScenario implements FixtureScenarioInterface
{
    /**
     * @inheritDoc
     */
    public function load(...$args)
    {
        return ResourceTypeFactory::make([
            ['slug' => ResourceType::SLUG_PASSWORD_STRING, 'definition' => SlugDefinition::passwordString()],
            [
                'slug' => ResourceType::SLUG_PASSWORD_AND_DESCRIPTION,
                'definition' => SlugDefinition::passwordAndDescription(),
            ],
        ])->persist();
    }
}
