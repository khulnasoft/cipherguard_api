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
 * @since         4.1.0
 */

namespace Cipherguard\Rbacs\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cipherguard\Rbacs\Model\Entity\UiAction;

/**
 * @property \Cipherguard\Rbacs\Model\Table\RbacsTable&\Cake\ORM\Association\HasMany $Rbacs
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction newEmptyEntity()
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction newEntity(array $data, array $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction[] newEntities(array $data, array $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction get($primaryKey, $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Cipherguard\Rbacs\Model\Entity\UiAction saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method iterable<\Cipherguard\Rbacs\Model\Entity\UiAction>|iterable<\Cake\Datasource\EntityInterface>|false saveMany(iterable $entities, $options = [])
 * @method iterable<\Cipherguard\Rbacs\Model\Entity\UiAction>|iterable<\Cake\Datasource\EntityInterface> saveManyOrFail(iterable $entities, $options = [])
 * @method iterable<\Cipherguard\Rbacs\Model\Entity\UiAction>|iterable<\Cake\Datasource\EntityInterface>|false deleteMany(iterable $entities, $options = [])
 * @method iterable<\Cipherguard\Rbacs\Model\Entity\UiAction>|iterable<\Cake\Datasource\EntityInterface> deleteManyOrFail(iterable $entities, $options = [])
 */
class UiActionsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('ui_actions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Rbacs', [
            'foreignKey' => 'foreign_id',
            'className' => 'Cipherguard/Rbacs.Rbacs',
            'conditions' => [
                'Rbacs.foreign_model' => 'UiAction',
            ],
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id', __('The identifier should be a valid UUID.'))
            ->allowEmptyString('id', __('The identifier should be not be empty.'), 'create');

        $validator
            ->ascii('name', __('The name should be a valid ASCII string.'))
            ->maxLength(
                'property',
                UiAction::NAME_MAX_LENGTH,
                __('The name length should be maximum {0} characters.', UiAction::NAME_MAX_LENGTH)
            )
            ->requirePresence('name', __('A name is required.'));

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name'], __('An action already exists for the given name.')));

        return $rules;
    }
}
