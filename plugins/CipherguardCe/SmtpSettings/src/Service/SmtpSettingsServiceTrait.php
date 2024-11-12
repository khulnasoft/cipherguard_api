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
namespace Cipherguard\SmtpSettings\Service;

trait SmtpSettingsServiceTrait
{
    /**
     * Silently remove fields that are not allowed in the data
     *
     * @param array $data data
     * @param array $allowedFields fields that are allowed in $data
     * @return array
     */
    private function sanitizeData(array $data, array $allowedFields): array
    {
        return array_intersect_key($data, array_flip($allowedFields));
    }
}
