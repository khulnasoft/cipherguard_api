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
 * @since         3.3.0
 */

namespace Cipherguard\JwtAuthentication\Test\TestCase\Service\VerifyToken;

use App\Model\Entity\AuthenticationToken;
use App\Test\Factory\AuthenticationTokenFactory;
use App\Utility\UuidFactory;
use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use Cipherguard\JwtAuthentication\Error\Exception\VerifyToken\ConsumedVerifyTokenAccessException;
use Cipherguard\JwtAuthentication\Error\Exception\VerifyToken\ExpiredVerifyTokenAccessException;
use Cipherguard\JwtAuthentication\Error\Exception\VerifyToken\InvalidVerifyTokenException;
use Cipherguard\JwtAuthentication\Service\VerifyToken\VerifyTokenValidationService;

/**
 * @covers \Cipherguard\JwtAuthentication\Service\RefreshToken\RefreshTokenRenewalService
 * @property \App\Model\Table\AuthenticationTokensTable $AuthenticationTokens
 */
class VerifyTokenValidationServiceTest extends TestCase
{
    /**
     * @var \Cipherguard\JwtAuthentication\Service\VerifyToken\VerifyTokenValidationService
     */
    public $service;

    public function setUp(): void
    {
        parent::setUp();
        Configure::write(VerifyTokenValidationService::VERIFY_TOKEN_EXPIRY_CONFIG_KEY, '1 hour');
        $this->service = new VerifyTokenValidationService();
        EventManager::instance()->setEventList(new EventList());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->service);
    }

    /**
     * Do not use data provider here, as data provider is compiled prior to the test suite.
     * We need a one minute old (or something small) token, and thus since the suite takes some time,
     * this token gets expired by the time the test is run.
     *
     * @throws \Exception
     */
    public function testVerifyTokenValidationService_Valid()
    {
        $this->expectNotToPerformAssertions();

        $expiry = FrozenTime::now()->addMinutes(1)->toUnixString();
        $this->service->validateToken($expiry, UuidFactory::uuid(), UuidFactory::uuid());
        $this->service->validateToken((int)$expiry, UuidFactory::uuid(), UuidFactory::uuid());
    }

    /**
     * @dataProvider invalidExpiryDates
     */
    public function testVerifyTokenValidationService_InvalidExpiryDate($expiry)
    {
        $this->expectException(InvalidVerifyTokenException::class);
        $this->expectExceptionMessage('Invalid verify token expiry.');
        $this->service->validateToken($expiry, 'Foo', 'Bar');
    }

    /**
     * @dataProvider expiredExpiryDates
     */
    public function testVerifyTokenValidationService_ExpiredExpiryDate($expiry)
    {
        $this->expectException(ExpiredVerifyTokenAccessException::class);
        $this->expectExceptionMessage('Attempt to access an expired verify token.');
        $this->service->validateToken($expiry, 'Foo', 'Bar');
        $this->assertEventFired(ExpiredVerifyTokenAccessException::class);
    }

    /**
     * @dataProvider invalidFormats
     */
    public function testVerifyTokenValidationService_InvalidFormat($token)
    {
        $this->expectException(InvalidVerifyTokenException::class);
        $this->expectExceptionMessage('Invalid verify token format.');
        $this->service->validateToken(FrozenTime::now()->addMinutes(1)->toUnixString(), $token, 'Bar');
    }

    public function testVerifyTokenValidationService_IsNotNonce()
    {
        $existingToken = AuthenticationTokenFactory::make(['id' => UuidFactory::uuid()])
            ->type(AuthenticationToken::TYPE_VERIFY_TOKEN)
            ->persist();
        $userId = $existingToken->user_id;
        $token = $existingToken->token;
        $this->expectException(ConsumedVerifyTokenAccessException::class);
        $this->expectExceptionMessage('Verify token has been already used in the past.');
        $this->service->validateToken(FrozenTime::now()->addMinutes(1)->toUnixString(), $token, $userId);
        $this->assertEventFired(ConsumedVerifyTokenAccessException::class);
    }

    public function invalidExpiryDates(): array
    {
        return [
            [null],
            [''],
            [FrozenTime::now()->addHours(5)->toUnixString()], // This is past the max validity of one hour
            [FrozenTime::now()->addMinutes(1)], // This is not a unix time!
        ];
    }

    public function expiredExpiryDates(): array
    {
        return [
            [FrozenTime::yesterday()->toUnixString()],
            [FrozenTime::now()->subSeconds(1)->toUnixString()],
        ];
    }

    public function invalidFormats(): array
    {
        return [
            [null],
            [''],
            ['ABC'],
        ];
    }
}
