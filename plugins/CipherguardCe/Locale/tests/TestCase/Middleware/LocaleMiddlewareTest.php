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

namespace Cipherguard\Locale\Test\TestCase\Middleware;

use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use Cake\I18n\I18n;
use Cipherguard\Locale\Service\RequestLocaleParserService;
use Cipherguard\Locale\Test\Factory\LocaleSettingFactory;

class LocaleMiddlewareTest extends AppIntegrationTestCase
{
    public function tearDown(): void
    {
        I18n::setLocale('en_UK');
        parent::tearDown();
    }

    public function testLocaleMiddlewareUnauthenticatedRequestWithOrgSetting()
    {
        $locale = 'fr_FR';
        LocaleSettingFactory::make()->locale($locale)->persist();

        $this->getJson('/auth/is-authenticated.json');
        $this->assertAuthenticationError();
        $this->assertSame($locale, I18n::getLocale());
    }

    public function testLocaleMiddlewareUnauthenticatedRequestWithQuerySetting(): void
    {
        $locale = 'fr_FR';
        $localeKey = RequestLocaleParserService::QUERY_KEY;
        LocaleSettingFactory::make()->locale($locale)->persist();

        $this->getJson('/auth/is-authenticated.json?' . $localeKey . '=' . $locale);
        $this->assertAuthenticationError();
        $this->assertSame($locale, I18n::getLocale());
    }

    public function testLocaleMiddlewareAuthenticatedWithAccountSetting(): void
    {
        $locale = 'fr_FR';
        LocaleSettingFactory::make()->locale($locale)->persist();

        $user = UserFactory::make()->user()->withLocale($locale)->persist();

        $this->logInAs($user);
        $this->getJson('/auth/is-authenticated.json');
        $this->assertResponseSuccess();
        $this->assertSame($locale, I18n::getLocale());
    }

    public function testLocaleMiddlewareAuthenticatedWithAccountSettingAndQuerySettings(): void
    {
        $locale = 'fr_FR';
        $localeKey = RequestLocaleParserService::QUERY_KEY;
        LocaleSettingFactory::make()->locale($locale)->persist();
        $user = UserFactory::make()->user()->withLocale('foo')->persist();

        $this->logInAs($user);
        $this->getJson('/auth/is-authenticated.json?' . $localeKey . '=' . $locale);
        $this->assertResponseSuccess();
        $this->assertSame('fr_FR', I18n::getLocale());
    }
}
