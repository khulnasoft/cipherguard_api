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
 * @since         2.0.0
 */

namespace App\Test\TestCase\Notification\Email;

use App\Notification\Email\CollectSubscribedEmailRedactorEvent;
use App\Notification\Email\EmailCollection;
use App\Notification\Email\EmailSubscriptionManager;
use App\Notification\Email\SubscribedEmailRedactorInterface;
use App\Notification\Email\SubscribedEmailRedactorTrait;
use Cake\Event\Event;
use PHPUnit\Framework\TestCase;

class SubscribedEmailRedactorTraitTest extends TestCase
{
    /**
     * @var SubscribedEmailRedactorInterface|callable
     */
    private $sut;

    /**
     * @var EmailSubscriptionManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subscriptionManagerMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->subscriptionManagerMock = $this->createMock(EmailSubscriptionManager::class);

        $this->sut = new class implements SubscribedEmailRedactorInterface {
            use SubscribedEmailRedactorTrait;

            public function getSubscribedEvents(): array
            {
                return [
                    'event_1',
                ];
            }

            /**
             * @inheritDoc
             */
            public function getNotificationSettingPath(): ?string
            {
                return null;
            }

            public function onSubscribedEvent(Event $event): EmailCollection
            {
                return new EmailCollection();
            }
        };
    }

    public function testThatIsInvokableAndCallSubscribe()
    {
        $this->subscriptionManagerMock->expects($this->once())
            ->method('addNewSubscription')
            ->with($this->sut);

        call_user_func($this->sut, CollectSubscribedEmailRedactorEvent::create($this->subscriptionManagerMock));
    }

    public function testThatSubscribeAddNewSubscriptionToManager()
    {
        $this->subscriptionManagerMock->expects($this->once())
            ->method('addNewSubscription')
            ->with($this->sut);

        $this->sut->subscribe(CollectSubscribedEmailRedactorEvent::create($this->subscriptionManagerMock));
    }

    public function testThatIsSubscribedToCollectSubscribedEmailRedactorEvent()
    {
        $this->assertEquals(
            [CollectSubscribedEmailRedactorEvent::EVENT_NAME => $this->sut],
            $this->sut->implementedEvents()
        );
    }
}
