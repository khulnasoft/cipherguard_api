<?php

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
 * @since         2.0.0
 */
use Cake\Core\Configure;

$overrideOptions = Configure::read('cipherguard.plugins.rememberMe.options');

Configure::load('Cipherguard/RememberMe.config', 'default', true);

if (isset($overrideOptions) && !empty($overrideOptions) && is_array($overrideOptions)) {
    Configure::write('cipherguard.plugins.rememberMe.options', $overrideOptions);
}
