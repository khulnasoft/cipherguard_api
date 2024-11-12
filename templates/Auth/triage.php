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
 */
use Cake\Core\Configure;

$this->assign('title', $title);
$version = Configure::read('cipherguard.version');

// See. fetch('scriptBottom')
$this->start('scriptBottom');
// Load the javascript application.
echo $this->Html->script('/js/app/api-vendors.js?v=' . $version, ['fullBase' => true, 'cache-version' => $version]);
echo $this->Html->script('/js/app/api-triage.js?v=' . $version, ['fullBase' => true, 'cache-version' => $version]);
$this->end();

$this->start('scriptTop');

echo $this->Html->script('/js/app/stylesheet.js?v=' . $version, [
    'id' => 'stylesheet-manager',
    'fullBase' => true,
    'data-file' => 'api_authentication.min.css',
    'cache-version' => $version]);

$this->end();
?>
