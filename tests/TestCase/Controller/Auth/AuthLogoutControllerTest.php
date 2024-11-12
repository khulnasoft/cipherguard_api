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
namespace App\Test\TestCase\Controller\Auth;

use App\Controller\Auth\AuthLogoutController;
use App\Test\Lib\AppIntegrationTestCase;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Laminas\Diactoros\Response\RedirectResponse;

class AuthLogoutControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Configure::write(AuthLogoutController::GET_LOGOUT_ENDPOINT_ENABLED_CONFIG, true);
    }

    /**
     * Check if a redirection is of type ZendRedirect
     * Usefull for high level routes redirections / route alias testing
     */
    public function assertZendRedirect(string $url): void
    {
        $this->assertTrue($this->_response instanceof RedirectResponse);
        $url = Router::url($url, true);
        $location = $this->_response->getHeader('location');
        $this->assertNotEmpty($location);
        $this->assertEquals($url, $location[0]);
    }

    // Test default POST method

    public function testAuthLogoutController_Success_PostMethod_Json_SignedIn(): void
    {
        $this->post('/auth/logout.json');
        $this->assertNoRedirect();
        $this->assertResponseContains('You are successfully logged out.');
    }

    public function testAuthLogoutController_Success_PostMethod_Json_NotSignedIn(): void
    {
        $this->logInAsUser();
        $this->post('/auth/logout.json');
        $this->assertResponseContains('You are successfully logged out.');
        $this->assertNoRedirect();
    }

    public function testAuthLogoutController_Success_PostMethod_NotJson(): void
    {
        $this->post('/auth/logout');
        $this->assertRedirect('/auth/login');
    }

    public function testAuthLogoutController_Error_CsrfToken(): void
    {
        $this->disableCsrfToken();

        $this->post('/auth/logout.json');
        $this->assertResponseError('Missing or incorrect CSRF cookie type.');
    }

    // Test unsecure GET method

    public function testAuthLogoutController_Success_GetMethod_Json_SignedIn(): void
    {
        $this->get('/auth/logout.json');
        $this->assertNoRedirect();
        $this->assertResponseContains('You are successfully logged out.');
    }

    public function testAuthLogoutController_Success_GetMethod_Json_NotSignedIn(): void
    {
        $this->logInAsUser();
        $this->get('/auth/logout.json');
        $this->assertResponseContains('You are successfully logged out.');
        $this->assertNoRedirect();
    }

    public function testAuthLogoutController_Success_GetMethod_NotJson(): void
    {
        $this->get('/auth/logout');
        $this->assertRedirect('/auth/login');
    }

    public function testAuthLogoutController_Success_GetMethod_NotJson_LogoutAlias(): void
    {
        $this->get('/logout');
        $this->assertZendRedirect('/auth/logout');
    }

    public function testAuthLogoutController_Error_GetMethod_GetLogoutEndpointDisabled()
    {
        Configure::write(AuthLogoutController::GET_LOGOUT_ENDPOINT_ENABLED_CONFIG, false);
        $this->get('/auth/logout');
        $this->assertResponseError('The logout route should only be accessed with POST method.');

        $this->get('/auth/logout.json');
        $this->assertResponseError('The logout route should only be accessed with POST method.');
    }
}
