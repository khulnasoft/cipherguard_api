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
$version = Configure::read('cipherguard.version');
$privacyUrl = Configure::read('cipherguard.legal.privacy_policy.url');
$termsUrl = Configure::read('cipherguard.legal.terms.url');
?>
<footer>
    <div class="footer">
        <ul class="footer-links">
<?php if (isset($safeMode) && !$safeMode) : ?>
            <li class="error message"><a href="https://help.cipherguard.github.io/faq/hosting/why-unsafe" title="terms of service">Unsafe mode</a></li>
<?php endif; ?>
<?php if (!empty($termsUrl)) : ?>
            <li><a href="<?php echo $termsUrl ?>"><?= __('Terms'); ?></a></li>
<?php endif; ?>
<?php if (!empty($privacyUrl)) : ?>
            <li><a href="<?php echo $privacyUrl ?>"><?= __('Privacy'); ?></a></li>
<?php endif; ?>
            <li><a href="https://www.cipherguard.github.io/credits"><?= __('Credits'); ?></a></li>
            <li id="version">
                <a href="https://www.cipherguard.github.io/credits" class="tooltip-left" data-tooltip="<?= $version; ?>">
                    <i class="fa fa-heart-o"></i>
                    <span class="visuallyhidden"><?= __('Versions'); ?></span>
                </a>
            </li>
        </ul>
    </div>
</footer>
