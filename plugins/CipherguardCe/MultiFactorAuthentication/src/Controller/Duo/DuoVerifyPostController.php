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
namespace Cipherguard\MultiFactorAuthentication\Controller\Duo;

use Cake\Http\Exception\GoneException;
use Cipherguard\MultiFactorAuthentication\Controller\MfaVerifyController;

class DuoVerifyPostController extends MfaVerifyController
{
    /**
     * @deprecated Inform that the Duo POST verify endpoint is not available anymore
     * @return \Cake\Http\Response
     */
    public function post()
    {
        $this->_assertRequestNotJson();

        throw new GoneException(__('This entrypoint is not available anymore.'));
    }
}
