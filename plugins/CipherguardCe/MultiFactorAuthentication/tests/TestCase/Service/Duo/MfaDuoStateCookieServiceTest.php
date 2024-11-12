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

namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Service\Duo;

use App\Utility\UuidFactory;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\MultiFactorAuthentication\Service\Duo\MfaDuoStateCookieService;

class MfaDuoStateCookieServiceTest extends TestCase
{
    use TruncateDirtyTables;

    public function testMfaDuoStateCookieService_createDuoStateCookie()
    {
        $token = UuidFactory::uuid();
        $service = new MfaDuoStateCookieService();
        $cookie = $service->createDuoStateCookie($token, true);

        $this->assertEquals($cookie->getName(), MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE);
        $this->assertEquals($cookie->getValue(), $token);
    }

    public function testMfaDuoStateCookieService_InvalidToken()
    {
        $token = 'invalid-token';
        $service = new MfaDuoStateCookieService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The authentication token should be a valid UUID.');
        $service->createDuoStateCookie($token, true);
    }

    public function testMfaDuoStateCookieService_readDuoStateCookieValue()
    {
        $token = UuidFactory::uuid();
        $request = new ServerRequest([
            'cookies' => [MfaDuoStateCookieService::MFA_COOKIE_DUO_STATE => $token],
        ]);
        $service = new MfaDuoStateCookieService();

        $tokenValue = $service->readDuoStateCookieValue($request);
        $this->assertEquals($tokenValue, $token);
    }
}
