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
 * @since         4.5.0
 *
 * @var string $title
 * @var string $text
 * @var \DateTimeInterface|string|int $datetime
 */
use Cake\I18n\FrozenTime;

?>
<span style="font-weight:bold;"><?= $title ?></span>
<br>
<span style=""><?= $text ?></span><br>
<span style="color:#888888">
    <?php
    echo FrozenTime::parse($datetime)->nice() . ' (' . date_default_timezone_get() . ')';
    ?>
</span><br>
