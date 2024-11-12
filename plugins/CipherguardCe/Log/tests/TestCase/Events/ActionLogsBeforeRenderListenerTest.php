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
 * @since         3.12.0
 */

namespace Cipherguard\Log\Test\TestCase\Events;

use App\Model\Entity\Role;
use App\Utility\UserAccessControl;
use App\Utility\UserAction;
use App\Utility\UuidFactory;
use Cake\Controller\Controller;
use Cake\Database\Exception\MissingConnectionException;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use Cipherguard\Log\Events\ActionLogsBeforeRenderListener;

/**
 * Class ActionLogsBeforeRenderListenerTest
 */
class ActionLogsBeforeRenderListenerTest extends TestCase
{
    public function exceptionsForNoDBConnection(): array
    {
        return [
            [new MissingConnectionException()],
            [new \PDOException()],
        ];
    }

    /**
     * @dataProvider exceptionsForNoDBConnection
     */
    public function testActionLogsBeforeRenderListener_On_MissingConnectionException_Should_Not_Fail($exception)
    {
        $accessControl = new UserAccessControl(Role::USER, UuidFactory::uuid('user.id.ada'));
        UserAction::getInstance($accessControl, 'Foo', 'Bar');

        $eventListener = new ActionLogsBeforeRenderListener();
        $this->assertSame(['Controller.beforeRender' => 'logControllerAction',], $eventListener->implementedEvents());

        $controllerStub = $this->getMockBuilder(Controller::class)->disableOriginalConstructor()->getMock();
        $controllerStub->method('getResponse')->willThrowException($exception);
        $event = new Event('Foo', $controllerStub);
        $eventListener->logControllerAction($event);
    }
}
