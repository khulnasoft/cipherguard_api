<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SARL (https://www.cipherguard.github.io)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SARL (https://www.cipherguard.github.io)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.github.io Cipherguard(tm)
 * @since         4.9.0
 */

namespace Cipherguard\Log\Test\TestCase\Controller\Secrets;

use App\Test\Factory\ResourceFactory;
use Cipherguard\Log\Test\Factory\SecretAccessFactory;
use Cipherguard\Log\Test\Lib\LogIntegrationTestCase;

class SecretsViewControllerLogTest extends LogIntegrationTestCase
{
    public function testSecretsViewController_Secret_Access()
    {
        $user = $this->logInAsUser();
        /** @var \App\Model\Entity\Resource $resource */
        $resource = ResourceFactory::make()
            ->withSecretsFor([$user])
            ->withPermissionsFor([$user])
            ->persist();

        $secret = $resource->secrets[0];

        $this->getJson("/secrets/resource/$resource->id.json");
        $this->assertSuccess();
        $this->assertNotNull($this->_responseJsonBody);
        $this->assertSecretAttributes($this->_responseJsonBody);

        $secretAccess = SecretAccessFactory::firstOrFail([
            'user_id' => $user->id,
            'resource_id' => $resource->id,
            'secret_id' => $secret->id,
        ]);
        $this->assertNotNull($secretAccess);
    }
}
