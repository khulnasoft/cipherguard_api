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
 * @since         4.0.0
 */
namespace App\Service\Cookie;

use Cake\Routing\Router;

class DefaultSecureCookieService extends AbstractSecureCookieService
{
    /**
     * @param string|null $path Cookie path
     * @return string
     */
    protected function getPath(?string $path): string
    {
        return Router::url($path);
    }
}
