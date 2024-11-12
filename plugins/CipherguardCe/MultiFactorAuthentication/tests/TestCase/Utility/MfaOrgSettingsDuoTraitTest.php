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
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\Utility\MfaOrgSettings;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class MfaOrgSettingsDuoTraitTest extends MfaIntegrationTestCase
{
    /**
     * @var \App\Model\Table\OrganizationSettingsTable
     */
    protected $OrganizationSettings;

    protected $defaultConfig = [
        'providers' => [
            MfaSettings::PROVIDER_DUO => true,
        ],
        // SEC-5652 Note to security researchers: these are not leaked credentials
        // They look valid as they should pass validation, but are fake
        MfaSettings::PROVIDER_DUO => [
            'clientId' => 'UICPIC93F14RWR5F55SJ',
            'clientSecret' => '8tkYNgi8aGAqa3KW1eqhsJLfjc1nJnHDYC1siNYX',
            'apiHostName' => 'api-45e9f2ca.duosecurity.com',
        ],
    ];

    /**
     * Setup.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->OrganizationSettings = TableRegistry::getTableLocator()->get('OrganizationSettings');
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetDuoProps()
    {
        Configure::write('cipherguard.plugins.multiFactorAuthentication', $this->defaultConfig);
        $settings = MfaOrgSettings::get()->getDuoOrgSettings();
        $this->assertNotEmpty($settings->getDuoApiHostname());
        $this->assertNotEmpty($settings->getDuoClientId());
        $this->assertNotEmpty($settings->getDuoClientSecret());
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetDuoIncompletePropsHostname()
    {
        $config = ['providers' => [MfaSettings::PROVIDER_DUO => true, ], MfaSettings::PROVIDER_DUO => []];
        $this->mockMfaOrgSettings($config);
        $settings = MfaOrgSettings::get()->getDuoOrgSettings();
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($settings->getDuoApiHostname());
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetDuoIncompletePropsSecretKey()
    {
        $config = ['providers' => [MfaSettings::PROVIDER_DUO => true, ], MfaSettings::PROVIDER_DUO => []];
        $this->mockMfaOrgSettings($config);
        $settings = MfaOrgSettings::get()->getDuoOrgSettings();
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($settings->getDuoClientSecret());
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetDuoIncompletePropsClientId()
    {
        $config = ['providers' => [MfaSettings::PROVIDER_DUO => true, ], MfaSettings::PROVIDER_DUO => []];
        $this->mockMfaOrgSettings($config);
        $settings = MfaOrgSettings::get()->getDuoOrgSettings();
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($settings->getDuoClientId());
    }
}
