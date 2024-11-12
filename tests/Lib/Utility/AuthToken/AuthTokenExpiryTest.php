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
 * @since         3.9.0
 */
namespace App\Test\Lib\Utility\AuthToken;

use App\Model\Entity\AuthenticationToken;
use App\Utility\AuthToken\AuthTokenExpiry;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

class AuthTokenExpiryTest extends TestCase
{
    /**
     * @var AuthTokenExpiry
     */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new AuthTokenExpiry();
    }

    public function testAuthTokenExpiry_GetExpirationForInvalidTokenTypeThrowAnException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->sut->getExpiryForTokenType('type');
    }

    public function testAuthTokenExpiry_GetExpirationForTokenRetrieveConfigurationForTokenType()
    {
        $tokenType = AuthenticationToken::TYPE_LOGIN;
        $expectedExpiry = '30 days';
        Configure::clear();
        Configure::write('cipherguard.auth.token.' . $tokenType . '.expiry', $expectedExpiry);
        Configure::write('cipherguard.auth.tokenExpiry', '10 days');

        $this->assertEquals($expectedExpiry, $this->sut->getExpiryForTokenType($tokenType));
    }

    public function testAuthTokenExpiry_GetExpirationForTokenFallbackToDefaultExpiryConfigurationIfExpiryNotDefinedForTokenType()
    {
        $tokenType = AuthenticationToken::TYPE_LOGIN;
        $expectedExpiry = '30 days';
        Configure::clear();
        Configure::write('cipherguard.auth.tokenExpiry', $expectedExpiry);

        $this->assertEquals($expectedExpiry, $this->sut->getExpiryForTokenType($tokenType));
    }

    public function testAuthTokenExpiry_GetExpirationForTokenFallbackToDefaultExpiryConfigurationIfExpiryInvalidForTokenType()
    {
        $tokenType = AuthenticationToken::TYPE_LOGIN;
        $expectedExpiry = '30 days';
        Configure::clear();
        Configure::write('cipherguard.auth.token.' . $tokenType . '.expiry', null);
        Configure::write('cipherguard.auth.tokenExpiry', $expectedExpiry);

        $this->assertEquals($expectedExpiry, $this->sut->getExpiryForTokenType($tokenType));
    }
}
