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
 * @since         3.6.0
 */
namespace App\Test\TestCase\Service\OpenPGP;

use App\Service\OpenPGP\PublicKeyRevocationCheckService;
use App\Test\Lib\AppTestCase;
use Cake\Core\Configure;

class PublicKeyRevocationCheckServiceTest extends AppTestCase
{
    public function testPublicKeyRevocationCheckService_Check_Success()
    {
        $armoredKey = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'PublicKeys' . DS . 'rsa4096_revoked_public.key');
        $this->assertTrue(PublicKeyRevocationCheckService::check($armoredKey));
    }

    public function testPublicKeyRevocationCheckService_Check_ErrorNotRevoked()
    {
        $armoredKey = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'PublicKeys' . DS . 'rsa4096_public.key');
        $this->assertFalse(PublicKeyRevocationCheckService::check($armoredKey));
    }

    public function testPublicKeyRevocationCheckService_Check_ErrorRevokedSigOnly()
    {
        $armoredKey = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'PublicKeys' . DS . 'revoked_sig_public.key');
        $this->assertFalse(PublicKeyRevocationCheckService::check($armoredKey));
    }

    public function testPublicKeyRevocationCheckService_Check_SuccessECC()
    {
        // See @TODO crypto check not implemented for non RSA keys
        $this->markTestIncomplete();
    }

    /**
     * By default, new signature Issuer, Issuer Fingerprint, and Embedded Signature subpackets generated by openpgpjs >= 5.5 have been moved to hashed subpackets.
     */
    public function testPublicKeyRevocationCheckService_Check_SuccessIssuerPacketInHashedSubPacketsOpenpgpjs550()
    {
        $armoredKey = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'PublicKeys' . DS . 'revoked_sig_public_hashed_packet_issuer.key');
        $this->assertTrue(PublicKeyRevocationCheckService::check($armoredKey));
        // Revoked key with unhashed issuer sub packet should not be accepted as revoked if disabled by gpg security flag.
        Configure::write('cipherguard.gpg.security.acceptRevokedKeyUnhashedIssuerSubPacket', false);
        $armoredKey = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'PublicKeys' . DS . 'revoked_sig_public_unhashed_packet_issuer.key');
        $this->assertFalse(PublicKeyRevocationCheckService::check($armoredKey));
    }

    /**
     * Since openpgpjs 5.7, most signature subpacket types have been marked as critical.
     */
    public function testPublicKeyRevocationCheckService_Check_SuccessCriticalSubpacketOpenpgpjs570()
    {
        $armoredKey = file_get_contents(FIXTURES . DS . 'OpenPGP' . DS . 'PublicKeys' . DS . 'revoked_sig_public_critical_sub_packets.key');
        $this->assertTrue(PublicKeyRevocationCheckService::check($armoredKey));
    }
}
