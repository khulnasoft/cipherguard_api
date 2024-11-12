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
 * @since         3.6.0
 */

namespace App\Model\Validation\GpgkeyType;

use App\Model\Validation\CipherguardValidationRule;
use App\Service\OpenPGP\PublicKeyValidationService;

class IsValidGpgkeyTypeValidationRule extends CipherguardValidationRule
{
    /**
     * @inheritDoc
     */
    public function defaultErrorMessage($value, $context): string
    {
        return __('The type should be one of the following: RSA, ECC, ECDSA, DH.');
    }

    /**
     * @inheritDoc
     */
    public function rule($value, $context): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // @deprecated switch to strict mode with v4
        // See PublicKeyValidationService::getStrictRules
        return PublicKeyValidationService::isValidAlgorithm($value, false);
    }
}
