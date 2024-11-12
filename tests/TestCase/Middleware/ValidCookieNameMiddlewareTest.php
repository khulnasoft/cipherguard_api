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
 * @since         4.5.0
 */

namespace App\Test\TestCase\Middleware;

use App\Middleware\ValidCookieNameMiddleware;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Cookie\CookieCollection;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \App\Middleware\ValidCookieNameMiddleware
 */
class ValidCookieNameMiddlewareTest extends TestCase
{
    public function testValidCookieNameMiddleware_Error()
    {
        $request = ServerRequestFactory::fromGlobals(['REQUEST_URI' => '/test']);
        // Mock cookie object because creating a new instance of the Cookie class throws exception (as it also check valid name).
        $cookieStub = $this->createStub(Cookie::class);
        $cookieStub->method('getName')->willReturn('foo,_bar');
        $cookieStub->method('getValue')->willReturn('test');
        $request = $request->withCookieCollection(new CookieCollection([$cookieStub]));
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('The cookie name `foo,_bar` contains invalid characters');
        $this->expectExceptionCode(400);

        (new ValidCookieNameMiddleware())->process($request, $handler);
    }
}
