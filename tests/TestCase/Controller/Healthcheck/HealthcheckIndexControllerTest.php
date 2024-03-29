<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         2.0.0
 */
namespace App\Test\TestCase\Controller\Healthcheck;

use App\Test\Lib\AppIntegrationTestCase;

class HealthcheckIndexControllerTest extends AppIntegrationTestCase
{
    public $fixtures = ['app.Base/Users', 'app.Base/Roles', 'app.Base/Profiles',];

    public function testHealthcheckIndexOk(): void
    {
        $this->get('/healthcheck');
        $this->assertResponseContains('Cipherguard API Status');
        $this->assertResponseOk();
    }

    public function testHealthcheckIndexJsonOk(): void
    {
        $this->getJson('/healthcheck.json');
        $this->assertResponseSuccess();
        $attributes = [
            'ssl', 'application', 'gpg', 'core', 'configFile', 'environment', 'database', 'smtpSettings',
        ];
        foreach ($attributes as $attr) {
            $this->assertObjectHasAttribute($attr, $this->_responseJsonBody);
        }
    }
}
