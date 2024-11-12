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
 * @since         3.10.0
 */

namespace Cipherguard\SmtpSettings\Test\TestCase;

use App\Utility\Application\FeaturePluginAwareTrait;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class SmtpSettingsEnvSettingBackwardCompatibilityTest extends TestCase
{
    use FeaturePluginAwareTrait;

    public function setUp(): void
    {
        parent::setUp();
        Configure::clear();
    }

    /**
     * Ensures backward compatibility of the SMTP Settings flag in config/default.php
     *
     * @dataProvider data
     */
    public function testSmtpSettingsEnvSettingBackwardCompatibility($envVariable, $envValue, $expectedResult)
    {
        putenv("$envVariable=$envValue");
        Configure::load('default');
        $this->assertSame($expectedResult, $this->isFeaturePluginEnabled('SmtpSettings'));
        putenv("$envVariable");
        $this->assertFalse(getenv($envVariable));
    }

    public function testSmtpSettingsEnvSettingBackwardCompatibility_Precedence_Over_Legacy()
    {
        putenv('CIPHERGUARD_PLUGINS_SMTP_SETTINGS_ENABLED=0');
        putenv('CIPHERGUARD_PLUGINS_SMTP_SETTINGS=1');
        Configure::load('default');
        $this->assertFalse($this->isFeaturePluginEnabled('SmtpSettings'));
        putenv('CIPHERGUARD_PLUGINS_SMTP_SETTINGS_ENABLED');
        putenv('CIPHERGUARD_PLUGINS_SMTP_SETTINGS');
    }

    public function data(): array
    {
        return [
            ['CIPHERGUARD_PLUGINS_SMTP_SETTINGS_ENABLED', 1, true],
            ['CIPHERGUARD_PLUGINS_SMTP_SETTINGS_ENABLED', 0, false],
            ['CIPHERGUARD_PLUGINS_SMTP_SETTINGS', 1, true],
            ['CIPHERGUARD_PLUGINS_SMTP_SETTINGS', 0, false],
            ['CIPHERGUARD_PLUGINS_SMTP', 0, true],
        ];
    }
}
