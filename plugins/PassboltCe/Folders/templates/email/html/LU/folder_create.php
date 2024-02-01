<?php
/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         2.13.0
 * @see \Cipherguard\Folders\Notification\Email\CreateFolderEmailRedactor
 * @var \App\View\AppView $this
 * @var array $body
 */

use App\Utility\Purifier;
use App\View\Helper\AvatarHelper;
use Cake\Routing\Router;

if (PHP_SAPI === 'cli') {
    Router::fullBaseUrl($body['fullBaseUrl']);
}
/** @var \App\Model\Entity\User $user */
$user = $body['user'];
/** @var \Cipherguard\Folders\Model\Entity\Folder $folder */
$folder = $body['folder'];

echo $this->element('Email/module/avatar', [
    'url' => AvatarHelper::getAvatarUrl($user['profile']['avatar']),
    'text' => $this->element('Email/module/avatar_text', [
        'user' => $user,
        'datetime' => $folder['created'],
        'text' => __('You have created a new folder'),
    ]),
]);

$text = __('Name: {0}', Purifier::clean($folder['name'])) . '<br/>';

echo $this->element('Email/module/text', [
    'text' => $text,
]);
echo $this->element('Email/module/button', [
    'url' => Router::url("/app/folders/view/{$folder['id']}", true),
    'text' => __('View it in cipherguard'),
]);
