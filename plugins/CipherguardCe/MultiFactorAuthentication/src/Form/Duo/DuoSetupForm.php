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
 * @since         2.5.0
 */
namespace Cipherguard\MultiFactorAuthentication\Form\Duo;

use App\Error\Exception\ValidationException;
use Cake\Http\Exception\InternalErrorException;
use Cipherguard\MultiFactorAuthentication\Utility\MfaAccountSettings;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class DuoSetupForm extends DuoVerifyForm
{
    /**
     * Form post validation treatment
     * Add DUO as verified provider
     *
     * @param array $data user submited data
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        try {
            MfaAccountSettings::enableProvider($this->uac, MfaSettings::PROVIDER_DUO, []);
        } catch (ValidationException $e) {
            throw new InternalErrorException('Could not save the Duo settings.', 500, $e);
        }

        return true;
    }
}
