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
 * @since         4.7.0
 */
namespace App\Test\TestCase\Service\Healthcheck\Core;

use App\Service\Healthcheck\Core\FullBaseUrlReachableCoreHealthcheck;
use Cake\Http\Client;
use Cake\TestSuite\TestCase;
use Psr\Http\Message\RequestInterface;

class FullBaseUrlReachableCoreHealthcheckTest extends TestCase
{
    public function testFullBaseUrlReachableCoreHealthcheck_On_Unresolved_Host_Should_Not_Throw_Exception(): void
    {
        $request = $this->getMockForAbstractClass(RequestInterface::class);
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $client->method('get')
            ->willThrowException(new Client\Exception\NetworkException('NetworkException not caught', $request));
        $service = new FullBaseUrlReachableCoreHealthcheck($client);
        $service->check();
        $this->assertFalse($service->isPassed());
        $this->assertTrue($service->isHealthcheckEndpointUnreachable());
    }
}
