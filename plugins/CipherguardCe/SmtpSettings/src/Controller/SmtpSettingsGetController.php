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
namespace Cipherguard\SmtpSettings\Controller;

use App\Controller\AppController;
use Cipherguard\SmtpSettings\Service\SmtpSettingsGetService;

class SmtpSettingsGetController extends AppController
{
    /**
     * SmtpSettings GET action
     *
     * @return void
     */
    public function get()
    {
        $this->User->assertIsAdmin();

        $settings = (new SmtpSettingsGetService())->getSettings();

        $this->success(__('The operation was successful.'), $settings);
    }
}
