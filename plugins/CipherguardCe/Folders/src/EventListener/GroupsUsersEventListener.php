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
 * @since         2.13.0
 */

namespace Cipherguard\Folders\EventListener;

use App\Service\GroupsUsers\GroupsUsersAddService;
use App\Service\GroupsUsers\GroupsUsersDeleteService;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cipherguard\Folders\Service\GroupsUsers\HandleGroupUserAddedService;
use Cipherguard\Folders\Service\GroupsUsers\HandleGroupUserDeletedService;

/**
 * Listen when:
 * - a group user is added;
 * - a group user is deleted.
 *
 * @package Cipherguard\Folders\EventListener
 */
class GroupsUsersEventListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            GroupsUsersDeleteService::AFTER_GROUP_USER_DELETED_EVENT_NAME => 'handleGroupUserDeletedEvent',
            GroupsUsersAddService::AFTER_GROUP_USER_ADDED_EVENT_NAME => 'handleGroupUserAddedEvent',
        ];
    }

    /**
     * Handle group user added event.
     *
     * @param \Cake\Event\Event $event The event.
     * @return void
     * @throws \Exception
     */
    public function handleGroupUserAddedEvent(Event $event)
    {
        $uac = $event->getData('accessControl');
        $groupUser = $event->getData('groupUser');
        $service = new HandleGroupUserAddedService();
        $service->handle($uac, $groupUser);
    }

    /**
     * Handle group user deleted event.
     *
     * @param \Cake\Event\Event $event The event.
     * @return void
     * @throws \Exception
     */
    public function handleGroupUserDeletedEvent(Event $event)
    {
        $groupUser = $event->getData('groupUser');
        $service = new HandleGroupUserDeletedService();
        $service->handle($groupUser);
    }
}
