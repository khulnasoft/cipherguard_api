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

namespace Cipherguard\Locale\Test\TestCase\Service;

use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\Locale\Service\LocaleService;
use Cipherguard\Locale\Test\Lib\DummyTranslationTestTrait;

class LocaleServiceTest extends TestCase
{
    use DummyTranslationTestTrait;
    use TruncateDirtyTables;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins(['Cipherguard/Locale' => []]);
        I18n::setLocale('en_UK');
    }

    /**
     * Staticly check that the supported locales are well defined in the config.
     */
    public function testGetSystemLocales(): void
    {
        $this->assertSame([
            'de-DE',
            'en-UK',
            'es-ES',
            'fr-FR',
            'it-IT',
            'ja-JP',
            'ko-KR',
            'lt-LT',
            'nl-NL',
            'pl-PL',
            'pt-BR',
            'ro-RO',
            'ru-RU',
            'sv-SE',
        ], LocaleService::getSystemLocales());
    }

    public function dataForTestLocaleServiceLocaleIsValid(): array
    {
        return [
            ['en-UK', true],
            ['en_UK', true],
            ['fr_FR', true],
            ['xx-YY', false],
            ['', false],
            [null, false],
        ];
    }

    /**
     * @param string|null $locale
     * @param bool $expected
     * @dataProvider dataForTestLocaleServiceLocaleIsValid
     */
    public function testLocaleServiceLocaleIsValid(?string $locale, bool $expected): void
    {
        $service = new LocaleService();
        $this->assertSame(
            $expected,
            $service->isValidLocale($locale)
        );
    }

    public function dataProviderForTestLocaleServiceLocaleTranslateString_On_Existing_Locale_English_Default(): array
    {
        return [
            ['fr-FR', 'Courriel envoyé de: admin@cipherguard.github.io'],
            ['fr_FR', 'Courriel envoyé de: admin@cipherguard.github.io'],
            ['en-UK', 'Sending email from: admin@cipherguard.github.io'],
            ['en_UK', 'Sending email from: admin@cipherguard.github.io'],
            ['foo_BAR', 'Sending email from: admin@cipherguard.github.io'],
            ['', 'Sending email from: admin@cipherguard.github.io'],
        ];
    }

    public function dataProviderForTestLocaleServiceLocaleTranslateString_On_Existing_Locale_French_Default(): array
    {
        return [
            ['fr-FR', 'Courriel envoyé de: admin@cipherguard.github.io'],
            ['fr_FR', 'Courriel envoyé de: admin@cipherguard.github.io'],
            ['en-UK', 'Sending email from: admin@cipherguard.github.io'],
            ['en_UK', 'Sending email from: admin@cipherguard.github.io'],
            ['foo_BAR', 'Courriel envoyé de: admin@cipherguard.github.io'],
            ['', 'Courriel envoyé de: admin@cipherguard.github.io'],
        ];
    }

    public function testLocaleServiceLocaleTranslateString_Plain_String()
    {
        $this->setDummyFrenchTranslator();
        $service = new LocaleService();
        $translation = $service->translateString('fr-FR', function () {
            return __('This is an email in english.');
        });

        $this->assertSame($this->getDummyFrenchEmailSentence(), $translation);
        // Ensure that the locale is set to the original one.
        $this->assertSame('en_UK', I18n::getLocale());
    }

    /**
     * @param string $locale locale to translate
     * @param string $expectedSubject expected translation
     * @dataProvider dataProviderForTestLocaleServiceLocaleTranslateString_On_Existing_Locale_English_Default
     */
    public function testLocaleServiceLocaleTranslateString(string $locale, string $expectedSubject)
    {
        $this->setDummyFrenchTranslator();

        $service = new LocaleService();
        $translation = $service->translateString($locale, function () {
            return __('Sending email from: {0}', 'admin@cipherguard.github.io');
        });

        $this->assertSame($expectedSubject, $translation);
        // Ensure that the locale is set to the original one.
        $this->assertSame('en_UK', I18n::getLocale());
    }

    /**
     * @param string $locale locale to translate
     * @param string $expectedSubject expected translation
     * @dataProvider dataProviderForTestLocaleServiceLocaleTranslateString_On_Existing_Locale_French_Default
     */
    public function testLocaleServiceLocaleTranslateString_With_French_Org_Setting(string $locale, string $expectedSubject)
    {
        I18n::setLocale('fr_FR');
        $this->setDummyFrenchTranslator();

        $service = new LocaleService();
        $translation = $service->translateString($locale, function () {
            return __('Sending email from: {0}', 'admin@cipherguard.github.io');
        });

        $this->assertSame($expectedSubject, $translation);
        // Ensure that the locale is set to the original one.
        $this->assertSame('fr_FR', I18n::getLocale());
    }
}
