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

namespace App\Test\TestCase\Controller\Pages;

use App\Test\Lib\AppIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\MultiFactorAuthenticationPlugin;

class HomeControllerTest extends AppIntegrationTestCase
{
    public $fixtures = [
        'app.Base/Users', 'app.Base/Profiles', 'app.Base/Gpgkeys', 'app.Base/Roles',
        'plugin.Cipherguard/AccountSettings.AccountSettings',
    ];

    public function testHomeNotLoggedInError(): void
    {
        $this->get('/app/passwords');
        $this->assertRedirectContains('/auth/login?redirect=%2Fapp%2Fpasswords');
    }

    public function testHomeSuccess(): void
    {
        $this->logInAsUser();
        $this->get('/app/passwords');
        $this->assertResponseOk();
        $this->assertResponseContains('skeleton');
    }

    /**
     * Make sure status code is 400 (not 5xx) when any set cookie name is invalid (contains special characters).
     *
     * @return void
     * @throws \Exception
     */
    public function testHome_InvalidCookieNameStatusCode(): void
    {
        $this->logInAsUser();
        $this->enableFeaturePlugin(MultiFactorAuthenticationPlugin::class);
        // Set invalid cookie
        $this->cookie('foo,_bar', 'test');

        $this->get('/app/passwords');

        $this->assertResponseCode(400);
        $this->assertResponseContains('The cookie name `foo,_bar` contains invalid characters');
    }
}
