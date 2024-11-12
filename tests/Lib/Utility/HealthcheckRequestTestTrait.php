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
 * @since         4.5.0
 */
namespace App\Test\Lib\Utility;

use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Routing\Router;

trait HealthcheckRequestTestTrait
{
    /**
     * @before
     * @after
     */
    public function clearMockResponses()
    {
        Client::clearMockResponses();
    }

    /**
     * @param int $code response code
     * @return Client
     */
    public function getMockedHealthcheckStatusRequest(int $code = 200, string $body = ''): Client
    {
        $client = new Client();
        $response = (new Response([], $body))->withStatus($code);
        $url = Router::url('/healthcheck/status.json', true);
        $client::addMockResponse('GET', $url, $response);

        return $client;
    }
}
