<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         4.2.0
 */
namespace Cipherguard\PasswordPolicies\Service;

use Cipherguard\PasswordPolicies\Model\Dto\PasswordPoliciesSettingsDto;

interface PasswordPoliciesGetSettingsInterface
{
    /**
     * Returns passwords policies settings.
     *
     * @return \Cipherguard\PasswordPolicies\Model\Dto\PasswordPoliciesSettingsDto
     * @throw FormValidationException If the settings does not validate.
     */
    public function get(): PasswordPoliciesSettingsDto;
}
