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
 * @since         3.10.0
 */
namespace Cipherguard\SelfRegistration\Service\DryRun;

interface SelfRegistrationDryRunServiceInterface
{
    /**
     * @return bool
     * @throws \Cake\Http\Exception\InternalErrorException if the settings in DB are not valid
     */
    public function isSelfRegistrationOpen(): bool;

    /**
     * @param array $data Data for identification
     * @return bool
     * @throws \Cake\Http\Exception\ForbiddenException if the user cannot self register.
     * @throws \App\Error\Exception\CustomValidationException if the domain of the user is not allowed by the settings.
     * @throws \Cake\Http\Exception\InternalErrorException if the settings in DB are not valid.
     */
    public function canGuestSelfRegister(array $data): bool;
}
