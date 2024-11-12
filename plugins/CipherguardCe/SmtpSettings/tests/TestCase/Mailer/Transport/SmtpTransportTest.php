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
 * @since         3.8.0
 */

namespace Cipherguard\SmtpSettings\Test\TestCase\Mailer\Transport;

use App\Error\Exception\FormValidationException;
use App\Mailer\Transport\DebugTransport;
use App\Mailer\Transport\SmtpTransport;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\Http\Exception\InternalErrorException;
use Cake\Mailer\Message;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\SmtpSettings\Service\SmtpSettingsSetService;
use Cipherguard\SmtpSettings\Test\Factory\SmtpSettingFactory;
use Cipherguard\SmtpSettings\Test\Lib\SmtpSettingsTestTrait;

/**
 * @covers \App\Mailer\Transport\SmtpTransport
 */
class SmtpTransportTest extends TestCase
{
    use SmtpSettingsTestTrait;
    use TruncateDirtyTables;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins(['Cipherguard/SmtpSettings' => []]);
        EventManager::instance()->setEventList(new EventList());
    }

    public function testSmtpTransport_With_Valid_DB_Settings()
    {
        $senderEmail = 'phpunit@cipherguard.github.io';
        $senderName = 'phpunit';
        $configInDb = $this->getSmtpSettingsData();
        $configInDb['sender_email'] = $senderEmail;
        $configInDb['sender_name'] = $senderName;
        $sender = [$senderEmail => $senderName];
        $this->encryptAndPersistSmtpSettings($configInDb);

        $transport = new DebugTransport();
        $message = new Message();
        $transport->send($message->setTo('john@cipherguard.github.io'));

        $this->assertInstanceOf(SmtpTransport::class, $transport);
        $message = $transport->getLastMessage();
        $this->assertSame($sender, $message->getFrom());
        $this->assertEventFired(SmtpTransport::SMTP_TRANSPORT_BEFORE_SEND_EVENT);
    }

    public function testSmtpTransport_Without_DB_Settings_Should_Return_File_Settings()
    {
        $configInFile = $this->getSmtpSettingsData();

        $transport = new SmtpTransport($configInFile);
        $configInTransport = $transport->getConfig();

        // The config in File should be returned
        $this->assertSettingsHaveTheRightKeyValues($configInFile, $configInTransport);
    }

    public function testSmtpTransport_In_DB_Not_Decryptable_Should_Return_File_Settings()
    {
        $this->gpgSetup();
        $this->gpg->setEncryptKeyFromFingerprint($this->serverKeyId);
        SmtpSettingFactory::make()->value('Foo')->persist();

        $this->expectException(InternalErrorException::class);
        $this->expectExceptionMessage('The OpenPGP server key cannot be used to decrypt the SMTP settings stored in database. To fix this problem, you need to configure the SMTP server again. Decryption failed.');
        $transport = new DebugTransport();
        $message = new Message();
        $transport->send($message->setTo('john@cipherguard.github.io'));
    }

    public function testSmtpTransport_No_Valid_Data_Should_Throw_Error()
    {
        $configInDb = $this->getSmtpSettingsData('sender_name', null); // This is not a valid data
        $this->encryptAndPersistSmtpSettings($configInDb);

        $this->expectException(FormValidationException::class);
        $this->expectExceptionMessage('Could not validate the smtp settings found in database.');

        $transport = new DebugTransport();
        $message = new Message();
        $transport->send($message->setTo('john@cipherguard.github.io'));
    }

    private function assertSettingsHaveTheRightKeyValues(array $configExpected, array $configInTransport)
    {
        foreach (SmtpSettingsSetService::SMTP_SETTINGS_ALLOWED_FIELDS as $v) {
            $this->assertSame($configExpected[$v], $configInTransport[$v]);
        }

        $excludedSettings = ['id', 'created', 'modified', 'created_by', 'modified_by'];
        foreach ($excludedSettings as $key) {
            $this->assertArrayNotHasKey($key, $configInTransport);
        }
    }
}
