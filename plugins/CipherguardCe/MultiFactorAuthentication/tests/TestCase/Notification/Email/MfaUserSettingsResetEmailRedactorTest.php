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
 * @since         2.12.0
 */

namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Notification\Email;

use App\Model\Entity\User;
use App\Test\Factory\UserFactory;
use App\Utility\UserAccessControl;
use App\Utility\UuidFactory;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use Cipherguard\MultiFactorAuthentication\Controller\UserSettings\MfaUserSettingsDeleteController;
use Cipherguard\MultiFactorAuthentication\Notification\Email\MfaUserSettingsResetEmailRedactor;

class MfaUserSettingsResetEmailRedactorTest extends TestCase
{
    /**
     * @var \Cipherguard\MultiFactorAuthentication\Notification\Email\MfaUserSettingsResetEmailRedactor
     */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new MfaUserSettingsResetEmailRedactor();
        $this->loadPlugins(['Cipherguard/Locale' => []]);
    }

    public function testThatEmailIsSubscribedToEvent()
    {
        $this->assertTrue(
            in_array(
                MfaUserSettingsDeleteController::MFA_USER_ACCOUNT_SETTINGS_DELETE_EVENT,
                $this->sut->getSubscribedEvents()
            )
        );
    }

    public function testThatEmailUseAdminDeleteTemplateWhenUserIsAdmin()
    {
        $adminUser = UserFactory::make()->admin()->persist();
        $user = UserFactory::make()->user()->willDisable()->persist();
        $user->set('locale', 'Foo');
        $uac = new UserAccessControl('admin', $adminUser->id, 'ada@cipherguard.github.io');
        $event = new Event(MfaUserSettingsDeleteController::MFA_USER_ACCOUNT_SETTINGS_DELETE_EVENT);
        $event->setData([
            'target' => $user,
            'uac' => $uac,
        ]);

        $emailCollection = $this->sut->onSubscribedEvent($event);
        $email = $emailCollection->getEmails()[0];

        $this->assertCount(1, $emailCollection->getEmails());
        $this->assertEquals(__('Your multi-factor authentication settings were reset by an administrator.'), $email->getSubject());
        $this->assertEquals(MfaUserSettingsResetEmailRedactor::TEMPLATE_ADMIN, $email->getTemplate());
        $emailData = $email->getData();
        $this->assertEquals('Multi-factor authentication settings were reset.', $emailData['title']);
        $this->assertEquals($adminUser->id, $emailData['body']['user']['id']);
    }

    public function testThatEmailUseSelfDeleteTemplateWhenUserIsHimself()
    {
        $userId = UuidFactory::uuid();
        $user = new User();
        $user->username = 'ada@cipherguard.github.io';
        $user->id = $userId;
        $user->locale = 'Foo';
        $user->disabled = null;
        $uac = new UserAccessControl('admin', $userId, 'ada@cipherguard.github.io');
        $event = new Event(MfaUserSettingsDeleteController::MFA_USER_ACCOUNT_SETTINGS_DELETE_EVENT);
        $event->setData([
            'target' => $user,
            'uac' => $uac,
        ]);

        $emailCollection = $this->sut->onSubscribedEvent($event);
        $email = $emailCollection->getEmails()[0];

        $this->assertCount(1, $emailCollection->getEmails());
        $this->assertEquals(__('Your multi-factor authentication settings were reset by you.'), $email->getSubject());
        $this->assertEquals(MfaUserSettingsResetEmailRedactor::TEMPLATE_SELF, $email->getTemplate());
        $this->assertEquals([
            'title' => __('Multi-factor authentication settings were reset.'),
            'body' => ['user' => $user],
        ], $email->getData());
    }
}
