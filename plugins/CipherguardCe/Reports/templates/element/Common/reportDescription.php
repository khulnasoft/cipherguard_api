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
 * @since         2.13.0
 * @var \App\View\AppView $this
 * @var array $report
 */
use App\Utility\Purifier;
use Cake\Http\Exception\InternalErrorException;

if (!isset($report)) {
    throw new InternalErrorException();
}

$reportDescription = Purifier::clean($report['description']);
?>
<div class="row description">
    <div class="col12">
        <p><strong><?= __('Description');?>:</strong>
            <?= $reportDescription; ?>
        </p>
    </div>
</div>
