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

namespace Cipherguard\SelfRegistration\Notification\Email\Redactor;

use App\Notification\Email\AbstractSubscribedEmailRedactorPool;
use Cipherguard\SelfRegistration\Notification\Email\Redactor\Settings\SelfRegistrationSettingsAdminEmailRedactor;
use Cipherguard\SelfRegistration\Notification\Email\Redactor\User\SelfRegistrationAdminEmailRedactor;

class SelfRegistrationEmailRedactorPool extends AbstractSubscribedEmailRedactorPool
{
    /**
     * @inheritDoc
     */
    public function getSubscribedRedactors(): array
    {
        $redactors = [];

        // This setting cannot be deactivated
        $redactors[] = new SelfRegistrationSettingsAdminEmailRedactor();
        $redactors[] = new SelfRegistrationAdminEmailRedactor();

        return $redactors;
    }
}
