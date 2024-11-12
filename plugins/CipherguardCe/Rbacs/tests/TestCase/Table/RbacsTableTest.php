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
 * @since         4.1.0
 */

namespace Cipherguard\Rbacs\Test\TestCase\Table;

use App\Test\Factory\RoleFactory;
use App\Test\Lib\Model\FormatValidationTrait;
use App\Utility\UuidFactory;
use Cake\ORM\TableRegistry;
use Cipherguard\Rbacs\Model\Entity\Rbac;
use Cipherguard\Rbacs\Model\Entity\UiAction;
use Cipherguard\Rbacs\Test\Factory\RbacFactory;
use Cipherguard\Rbacs\Test\Factory\UiActionFactory;
use Cipherguard\Rbacs\Test\Lib\RbacsTestCase;

class RbacsTableTest extends RbacsTestCase
{
    use FormatValidationTrait;

    /**
     * @var \Cipherguard\Rbacs\Model\Table\RbacsTable
     */
    public $Rbacs;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Rbacs = TableRegistry::getTableLocator()->get('Cipherguard/Rbacs.Rbacs');
    }

    /**
     * Get default Rbacs entity options.
     */
    public function getDummyRbacsEntityDefaultOptions(): array
    {
        return [
            'checkRules' => true,
            'accessibleFields' => [
                '*' => true,
            ],
        ];
    }

    public function testRbacsTable_ValidationForeignModel(): void
    {
        $testCases = [
            'inList' => self::getInListTestCases(Rbac::ALLOWED_FOREIGN_MODELS),
        ];
        $data = RbacFactory::make()->getEntity()->toArray();
        $this->assertFieldFormatValidation($this->Rbacs, 'foreign_model', $data, self::getDummyRbacsEntityDefaultOptions(), $testCases);
    }

    public function testRbacsTable_ValidationForeignId(): void
    {
        $testCases = [
            'uuid' => self::getUuidTestCases(),
        ];
        $data = RbacFactory::make()->getEntity()->toArray();
        $this->assertFieldFormatValidation($this->Rbacs, 'foreign_id', $data, self::getDummyRbacsEntityDefaultOptions(), $testCases);
    }

    public function testRbacsTable_ValidationControlFunction(): void
    {
        $testCases = [
            'inList' => self::getInListTestCases(Rbac::ALLOWED_CONTROL_FUNCTIONS),
        ];
        $data = RbacFactory::make()->getEntity()->toArray();
        $this->assertFieldFormatValidation($this->Rbacs, 'control_function', $data, self::getDummyRbacsEntityDefaultOptions(), $testCases);
    }

    public function testRbacsTable_ValidationRoleId(): void
    {
        $testCases = [
            'uuid' => self::getUuidTestCases(),
        ];
        $data = RbacFactory::make()->getEntity()->toArray();
        $this->assertFieldFormatValidation($this->Rbacs, 'role_id', $data, self::getDummyRbacsEntityDefaultOptions(), $testCases);
    }

    public function testRbacsTable_ValidationCreatedBy(): void
    {
        $testCases = [
            'uuid' => self::getUuidTestCases(),
        ];
        $data = RbacFactory::make()->getEntity()->toArray();
        $this->assertFieldFormatValidation($this->Rbacs, 'created_by', $data, self::getDummyRbacsEntityDefaultOptions(), $testCases);
    }

    public function testRbacsTable_ValidationModifiedBy(): void
    {
        $testCases = [
            'uuid' => self::getUuidTestCases(),
        ];
        $data = RbacFactory::make()->getEntity()->toArray();
        $this->assertFieldFormatValidation($this->Rbacs, 'modified_by', $data, self::getDummyRbacsEntityDefaultOptions(), $testCases);
    }

    public function testRbacsTable_BuildRules_AllowedControlFunctionForUiAction_Valid(): void
    {
        $userRole = RoleFactory::make()->user()->persist();
        $uiAction = UiActionFactory::make()->name(UiAction::NAME_RESOURCES_IMPORT)->persist();
        $data = [
            'role_id' => $userRole->get('id'),
            'control_function' => Rbac::CONTROL_FUNCTION_ALLOW,
            'foreign_model' => Rbac::FOREIGN_MODEL_UI_ACTION,
            'foreign_id' => $uiAction->get('id'),
        ];
        $rbac = $this->Rbacs->newEntity($data, [
            'accessibleFields' => [
                'role_id' => true,
                'control_function' => true,
                'foreign_model' => true,
                'foreign_id' => true,
            ],
        ]);

        $result = $this->Rbacs->save($rbac);

        $this->assertInstanceOf(Rbac::class, $result);
    }

    public function testRbacsTable_BuildRules_AllowedControlFunctionForUiAction_Invalid(): void
    {
        $userRole = RoleFactory::make()->user()->persist();
        $uiAction = UiActionFactory::make()->name(UiAction::NAME_RESOURCES_IMPORT)->persist();
        $data = [
            'role_id' => $userRole->get('id'),
            'control_function' => Rbac::CONTROL_FUNCTION_ALLOW_IF_GROUP_MANAGER_IN_ONE_GROUP,
            'foreign_model' => Rbac::FOREIGN_MODEL_UI_ACTION,
            'foreign_id' => $uiAction->get('id'),
        ];
        $rbac = $this->Rbacs->newEntity($data, [
            'accessibleFields' => [
                'role_id' => true,
                'control_function' => true,
                'foreign_model' => true,
                'foreign_id' => true,
            ],
        ]);

        $result = $this->Rbacs->save($rbac);

        $this->assertFalse($result);
        $errors = $rbac->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('isControlFunctionAllowed', $errors['control_function']);
    }

    public function testRbacsTable_BuildRules_AllowedControlFunctionForUiAction_Invalid_ForeignIdNotPresent(): void
    {
        $userRole = RoleFactory::make()->user()->persist();
        UiActionFactory::make()->name(UiAction::NAME_RESOURCES_IMPORT)->persist();
        $data = [
            'role_id' => $userRole->get('id'),
            'control_function' => Rbac::CONTROL_FUNCTION_ALLOW_IF_GROUP_MANAGER_IN_ONE_GROUP,
            'foreign_model' => Rbac::FOREIGN_MODEL_UI_ACTION,
            'foreign_id' => UuidFactory::uuid(),
        ];
        $rbac = $this->Rbacs->newEntity($data, [
            'accessibleFields' => [
                'role_id' => true,
                'control_function' => true,
                'foreign_model' => true,
                'foreign_id' => true,
            ],
        ]);

        $result = $this->Rbacs->save($rbac);

        $this->assertFalse($result);
        $errors = $rbac->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('isControlFunctionAllowed', $errors['control_function']);
    }
}
