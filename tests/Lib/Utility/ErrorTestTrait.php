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
 * @since         3.7.0
 */
namespace App\Test\Lib\Utility;

use App\Error\Exception\ValidationException;
use Cake\Utility\Hash;

trait ErrorTestTrait
{
    /**
     * Asserts a validation exception.
     *
     * @param string $errorMessage Expected error message.
     * @param string|null $errorFieldName Expected field to return an error.
     * @return void
     */
    protected function assertValidationException(ValidationException $e, string $errorMessage, ?string $errorFieldName = null): void
    {
        $this->assertEquals($errorMessage, $e->getMessage());
        if ($errorFieldName) {
            $error = Hash::get($e->getErrors(), $errorFieldName);
            $this->assertNotNull($error, "Expected error field not found : {$errorFieldName}. Errors: " . json_encode($e->getErrors()));
        }
    }
}
