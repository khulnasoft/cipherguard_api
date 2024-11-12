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
 * @since         3.8.0
 */
namespace App\Error\Exception;

use Cake\Http\Exception\InternalErrorException;

/**
 * Exception raised when a validation rule is not satisfied in a Form.
 */
class NoAdminInDbException extends InternalErrorException
{
    /**
     * Constructor.
     *
     * @param string|null $message The error message
     */
    public function __construct(?string $message = null)
    {
        $message = $message ?? __('No admin were found in the database.');
        parent::__construct($message);
    }
}
