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
 * @since         3.2.0
 */

namespace Cipherguard\Locale\Test\TestCase\Controller;

use App\Test\Lib\AppIntegrationTestCase;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Locale\Service\GetOrgLocaleService;
use Cipherguard\Locale\Service\LocaleService;

/**
 * Class OrganizationLocalesSelectControllerTest
 */
class OrganizationLocalesSelectControllerTest extends AppIntegrationTestCase
{
    use LocatorAwareTrait;

    /**
     * @var \App\Model\Table\OrganizationSettingsTable
     */
    protected $OrganizationSettings;

    public function setUp(): void
    {
        parent::setUp();
        $this->OrganizationSettings = $this->fetchTable('OrganizationSettings');
    }

    public function tearDown(): void
    {
        GetOrgLocaleService::clearOrganisationLocale();
        parent::tearDown();
    }

    public function testOrganizationLocalesSelectAsGuestFails()
    {
        $this->logInAsUser();
        $this->postJson('/locale/settings.json');
        $this->assertForbiddenError('Access restricted to administrators.');
    }

    public function testOrganizationLocalesSelectSuccess()
    {
        $this->logInAsAdmin();
        $value = 'en-UK';
        $this->postJson('/locale/settings.json', compact('value'));
        $this->assertResponseSuccess();
        $this->assertSame(
            $value,
            $this->OrganizationSettings->getByProperty(LocaleService::SETTING_PROPERTY)->get('value')
        );
    }

    public function testOrganizationLocalesSelectOnNonSupportedLocal()
    {
        $this->logInAsAdmin();
        $value = 'foo-BAR';
        $this->postJson('/locale/settings.json', compact('value'));
        $this->assertBadRequestError('This is not a valid locale.');
    }
}
