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
 * @copyright     Copyright (c) Cipherguard SARL (https://www.cipherguard.github.io)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.github.io Cipherguard(tm)
 * @since         3.7.0
 */

namespace Cipherguard\Log\Test\Factory;

use App\Test\Factory\ResourceFactory;
use Cake\Chronos\Chronos;
use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Faker\Generator;
use Cipherguard\Log\Model\Entity\EntityHistory;

/**
 * ActionLogFactory
 *
 * @method \Cipherguard\Log\Model\Entity\EntityHistory|\Cipherguard\Log\Model\Entity\EntityHistory[] persist()
 * @method \Cipherguard\Log\Model\Entity\EntityHistory getEntity()
 * @method \Cipherguard\Log\Model\Entity\EntityHistory[] getEntities()
 * @method static \Cipherguard\Log\Model\Entity\EntityHistory get($primaryKey, array $options = [])
 */
class EntitiesHistoryFactory extends CakephpBaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'Cipherguard/Log.EntitiesHistory';
    }

    /**
     * Defines the factory's default values. This is useful for
     * not nullable fields. You may use methods of the present factory here too.
     *
     * @return void
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'action_log_id' => $faker->uuid(),
                'foreign_model' => $faker->word(),
                'foreign_key' => $faker->uuid(),
                'crud' => $faker->randomLetter(),
                'created' => Chronos::now()->subMinutes($faker->randomNumber(8)),
            ];
        });
    }

    /**
     * @param ?ActionLogFactory $actionLogFactory ActionLog factory
     * @return $this
     */
    public function withActionLog(?ActionLogFactory $actionLogFactory = null)
    {
        return $this->with('ActionLogs', $actionLogFactory);
    }

    /**
     * @param ?ResourceFactory $resourceFactory Resource factory
     * @return $this
     */
    public function withResource(?ResourceFactory $resourceFactory = null)
    {
        return $this->resources()->with('Resources', $resourceFactory);
    }

    /**
     * @param ?ResourceFactory $resourceFactory Resource factory
     * @return $this
     */
    public function withSecretAccessOnResource(?ResourceFactory $resourceFactory = null)
    {
        return $this->secretAccesses()->with('SecretAccesses.Resources', $resourceFactory);
    }

    /**
     * @return $this
     */
    public function users()
    {
        return $this->setField('foreign_model', 'Users');
    }

    /**
     * @return $this
     */
    public function resources()
    {
        return $this->setField('foreign_model', 'Resources');
    }

    /**
     * @return $this
     */
    public function secretAccesses()
    {
        return $this->setField('foreign_model', 'SecretAccesses');
    }

    /**
     * @return $this
     */
    public function create()
    {
        return $this->setField('crud', EntityHistory::CRUD_CREATE);
    }
}
