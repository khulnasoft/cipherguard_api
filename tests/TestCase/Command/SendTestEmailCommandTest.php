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
 * @since         3.1.0
 */
namespace App\Test\TestCase\Command;

use App\Mailer\Transport\DebugTransport;
use App\Test\Lib\AppTestCase;
use App\Test\Lib\Utility\EmailTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Mailer\TransportFactory;
use Cipherguard\SmtpSettings\Service\SmtpSettingsSendTestMailerService;
use Cipherguard\SmtpSettings\Test\Lib\SmtpSettingsIntegrationTestTrait;

/**
 * @covers \App\Command\SendTestEmailCommand
 */
class SendTestEmailCommandTest extends AppTestCase
{
    use ConsoleIntegrationTestTrait;
    use EmailTestTrait;
    use SmtpSettingsIntegrationTestTrait;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->useCommandRunner();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        // Reset state
        $defaultConfig = [
            'className' => DebugTransport::class,
            'host' => 'unreachable_host.dev',
            'port' => 123,
            'timeout' => 30,
            'username' => 'foo',
            'password' => 'bar',
            'client' => null,
            'tls' => true,
        ];
        TransportFactory::drop('default');
        TransportFactory::setConfig('default', $defaultConfig);

        parent::tearDown();
    }

    /**
     * Basic help test
     */
    public function testSendTestEmailCommandHelp()
    {
        $this->exec('cipherguard send_test_email -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Try to send a test email and display debug information.');
        $this->assertOutputContains('cake cipherguard send_test_email');
    }

    /**
     * Basic test without recipient should fail.
     */
    public function testSendTestEmailCommandWithoutRecipient()
    {
        $this->exec('cipherguard send_test_email');

        $this->assertExitError();
        $this->assertErrorContains('The `recipient` option is required and has no default value');
    }

    /**
     * Basic test with recipient
     */
    public function testSendTestEmailCommandWithRecipient()
    {
        $config = TransportFactory::getConfig('default');
        $base64encodedString = base64_encode(
            chr(0) . $config['username'] . chr(0) . $config['password']
        );
        $trace = [['cmd' => 'Password: ' . $base64encodedString]];
        $recipient = 'test@cipherguard.test';
        $this->mockService(SmtpSettingsSendTestMailerService::class, function () use ($trace) {
            $service = $this->getMockBuilder(SmtpSettingsSendTestMailerService::class)
                ->onlyMethods(['getTrace'])
                ->getMock();
            $service->method('getTrace')->willReturn($trace);

            return $service;
        });

        $this->exec('cipherguard send_test_email -r ' . $recipient);

        $this->assertExitSuccess();
        $this->assertOutputContains('<info>Trace</info>');
        $this->assertOutputContains('<info> Password: *****</info>');
        $this->assertMailSentToAt(0, [$recipient => $recipient]);
        $this->assertMailSubjectContainsAt(0, 'Cipherguard test email');
        $this->assertMailCount(1);
    }

    /**
     * Basic test with invalid recipient
     */
    public function testSendTestEmailCommandWithInvalidRecipient()
    {
        $recipient = 'this is not a valid recipient';
        $this->exec('cipherguard send_test_email -r ' . $recipient);
        $this->assertExitError();
        $this->assertOutputContains('The recipient should be a valid email address.');
    }

    /**
     * Basic test with non Smtp config will fail
     */
    public function testSendTestEmailCommandWithConfigNotSmtp()
    {
        $config = TransportFactory::getConfig('default');
        $config['className'] = 'notSmtp';
        TransportFactory::drop('default');
        TransportFactory::setConfig('default', $config);

        $this->exec('cipherguard send_test_email -r test@cipherguard.test');

        $this->assertExitError();
        $this->assertOutputContains('Your email transport configuration is not set to use "Smtp"');
    }
}
