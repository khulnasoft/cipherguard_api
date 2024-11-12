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
 * @since         3.10.0
 */

namespace App\Test\TestCase\Middleware;

use App\Middleware\HttpProxyMiddleware;
use App\Test\Lib\Http\TestRequestHandler;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\TestSuite\TestCase;

/**
 * Test for HttpProxyMiddleware
 */
class HttpProxyMiddlewareTest extends TestCase
{
    /**
     * @var string
     */
    private $remoteAddr;

    /**
     * @var string
     */
    private $xRealIp;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->remoteAddr = env('REMOTE_ADDR');
        $this->xRealIp = env('HTTP_X_REAL_IP');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        putenv('REMOTE_ADDR=' . $this->remoteAddr);
        putenv('HTTP_X_REAL_IP=' . $this->xRealIp);
        parent::tearDown();
    }

    public function testHttpProxyMiddlewareTest_No_Proxy()
    {
        $realClientIP = '1.2.3.4';
        $request = ServerRequestFactory::fromGlobals(['REMOTE_ADDR' => $realClientIP]);
        // Mock response
        $response = new Response();
        $requestHandler = new TestRequestHandler(function ($request) use ($response) {
            return $response;
        });

        $middleware = new HttpProxyMiddleware();
        $middleware->process($request, $requestHandler);

        $this->assertEmpty($response->getHeader('Access-Control-Expose-Headers'));
        $this->assertEquals($request->clientIp(), $realClientIP);
    }

    public function testHttpProxyMiddlewareTest_With_Proxy_With_Security_Activated_Should_Set_Headers_White_List_In_Response()
    {
        $realClientIP = '2.3.4.5';
        $proxyIP = '1.2.3.4';
        Configure::write(HttpProxyMiddleware::CIPHERGUARD_SECURITY_PROXIES_ACTIVE_CONFIG_NAME, true);
        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => $proxyIP,
            'HTTP_X_REAL_IP' => $realClientIP,
        ]);
        $response = new Response();
        $requestHandler = new TestRequestHandler(function ($request) use ($response) {
            return $response;
        });

        $middleware = new HttpProxyMiddleware();
        $response = $middleware->process($request, $requestHandler);

        $this->assertEquals(
            HttpProxyMiddleware::HTTP_HEADERS_WHITELIST,
            $response->getHeader(HttpProxyMiddleware::ACCESS_CONTROL_EXPOSE_HEADERS)
        );
        $this->assertEquals($request->clientIp(), $realClientIP);
        Configure::write(HttpProxyMiddleware::CIPHERGUARD_SECURITY_PROXIES_ACTIVE_CONFIG_NAME, false);
    }

    public function testHttpProxyMiddlewareTest_With_Proxy_With_Security_Deactivated_Should_Not_Set_Headers_White_List_In_Response()
    {
        $realClientIP = '2.3.4.5';
        $proxyIP = '1.2.3.4';
        Configure::write(HttpProxyMiddleware::CIPHERGUARD_SECURITY_PROXIES_ACTIVE_CONFIG_NAME, false);
        $request = ServerRequestFactory::fromGlobals([
            'REMOTE_ADDR' => $proxyIP,
            'HTTP_X_REAL_IP' => $realClientIP,
        ]);

        $response = new Response();
        $requestHandler = new TestRequestHandler(function ($request) use ($response) {
            return $response;
        });

        $middleware = new HttpProxyMiddleware();
        $response = $middleware->process($request, $requestHandler);

        $this->assertEmpty(
            $response->getHeader(HttpProxyMiddleware::ACCESS_CONTROL_EXPOSE_HEADERS)
        );
        $this->assertEquals($request->clientIp(), $proxyIP);
    }
}
