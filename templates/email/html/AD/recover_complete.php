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
 * @since         3.6.0
 */
use App\Utility\Purifier;
use App\View\Helper\AvatarHelper;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

if (PHP_SAPI === 'cli') {
    Router::fullBaseUrl($body['fullBaseUrl']);
}
$admin = $body['admin'];
$user = $body['user'];
$clientIp = $body['clientIp'];
$userAgent = $body['userAgent'];
$username = Purifier::clean($user['username']);
$userFirstName = Purifier::clean($user['profile']['first_name']);

echo $this->element('Email/module/avatar',[
    'url' => AvatarHelper::getAvatarUrl($user['profile']['avatar']),
    'text' => $this->element('Email/module/avatar_text', [
        'user' => $user,
        'datetime' => FrozenTime::now(),
        'text' => $title,
    ])
]);

$text = ' ' . __('{0} ({1}) just completed an account recovery. Feel free to get in touch with this user if you feel this action looks suspicious.', $userFirstName, $username);
echo $this->element('Email/module/text', [
    'text' => $text
]);
echo $this->element('Email/module/user_info', compact('userAgent', 'clientIp'));

echo $this->element('Email/module/button', [
    'url' => Router::url('/app/users/view/' . $user['id'] , true),
    'text' => __('View user in cipherguard')
]);
