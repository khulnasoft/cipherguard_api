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
 * @since         2.5.0
 */
namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\Utility\MfaOtpFactory;
use stdClass;

class MfaOtpFactoryTest extends MfaIntegrationTestCase
{
    /**
     * @group mfa
     * @group mfaOtpFactory
     */
    public function testMfaOtpFactoryGetIssuer()
    {
        $issuer = MfaOtpFactory::getIssuer();
        $this->assertTextEndsNotWith('/', $issuer);
        $this->assertTextStartsNotWith('http', $issuer);
        $this->assertTextNotContains('://', $issuer);
    }

    /**
     * @group mfa
     * @group mfaOtpFactory
     */
    public function testMfaOtpFactoryGetIssuer_UrlCheck()
    {
        $issuer = MfaOtpFactory::getIssuer('https://localhost:8080');
        $this->assertTextEquals('localhost', $issuer);

        $issuer = MfaOtpFactory::getIssuer('http://cloud.cipherguard.github.io/acme');
        $this->assertTextEquals('cloud.cipherguard.github.io/acme', $issuer);

        $issuer = MfaOtpFactory::getIssuer('http://cloud.cipherguard.github.io/acme:test');
        $this->assertTextEquals('cloud.cipherguard.github.io/acmetest', $issuer);

        $issuer = MfaOtpFactory::getIssuer('www.cipherguard.github.io');
        $this->assertTextEquals('www.cipherguard.github.io', $issuer);
    }

    /**
     * @group mfa
     * @group mfaOtpFactory
     */
    public function testMfaOtpQrCodeInline()
    {
        $otp = MfaOtpFactory::generateTOTP($this->mockUserAccessControl('ada'));
        $qrcode = MfaOtpFactory::getQrCodeInlineSvg($otp);
        $this->assertStringNotContainsString('<?xml version="1.0" encoding="UTF-8"?>', $qrcode);
        $this->assertStringContainsString('<svg', $qrcode);
    }

    /**
     * @group mfa
     * @group mfaOtpFactory
     */
    public function testMfaOtpFactoryGenerateTOTP()
    {
        $otp = MfaOtpFactory::generateTOTP($this->mockUserAccessControl('ada'));
        $this->assertTrue(true);
        $this->assertStringContainsString('otpauth://totp/', $otp);
        $issuer = MfaOtpFactory::getIssuer();
        $this->assertStringContainsString('issuer=' . $issuer, $otp);
        $this->assertStringContainsString('secret=', $otp);
        $this->assertStringContainsString('ada%40cipherguard.github.io', $otp);
    }

    public function dataForTestGenerateTOTP()
    {
        return [
            [256], // legacy
            [32], // new default, also library default
            [20], // custom value requested by users, below this length is getting less secure
            [16], // minimum value recommended, less secure
            [8], // custom length, less secure, that should be replaced with 16 bytes in length
            [null],
            ['32'],
            ['thirty two'],
            [false],
            [[]],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider dataForTestGenerateTOTP
     */
    public function testMfaOtpFactoryTest_generateTOTP_Multiple_Cases($secretLength)
    {
        $originalSecretLength = Configure::read(MfaOtpFactory::CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH);
        Configure::write(MfaOtpFactory::CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH, $secretLength);

        $otp = MfaOtpFactory::generateTOTP($this->mockUserAccessControl('ada'));
        $qrcode = MfaOtpFactory::getQrCodeInlineSvg($otp);
        $this->assertStringNotContainsString('<?xml version="1.0" encoding="UTF-8"?>', $qrcode);
        $this->assertStringContainsString('<svg', $qrcode);

        Configure::write(MfaOtpFactory::CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH, $originalSecretLength);
    }

    public function dataForTestGetAndSanitizeSecretLengthFromConfig()
    {
        return [
            [256, 256],
            [32, 32],
            [20, 20],
            [16, 16],
            [8, 16],
            [null, 32],
            ['32', 32],
            ['4', 16],
            ['thirty two', 32],
            [false, 32],
            [[], 32],
            [new stdClass(), 32],
        ];
    }

    /**
     * @dataProvider dataForTestGetAndSanitizeSecretLengthFromConfig
     */
    public function testMfaOtpFactoryTest_getAndSanitizeSecretLengthFromConfig_Multiple_Cases(
        $secretLength,
        $sanitizedLength
    ) {
        $originalSecretLength = Configure::read(MfaOtpFactory::CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH);
        Configure::write(MfaOtpFactory::CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH, $secretLength);

        $len = MfaOtpFactory::getAndSanitizeSecretLengthFromConfig();
        $this->assertEquals($len, $sanitizedLength);

        Configure::write(MfaOtpFactory::CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH, $originalSecretLength);
    }
}
