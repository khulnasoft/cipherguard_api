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
 * @since         3.3.0
 */
namespace Cipherguard\JwtAuthentication\Event;

use App\Middleware\ContainerAwareMiddlewareTrait;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Event\EventListenerInterface;
use Cake\Http\Cookie\Cookie;
use Cipherguard\JwtAuthentication\Service\Middleware\JwtRequestDetectionService;

class RemoveSessionCookiesOnJwt implements EventListenerInterface
{
    use ContainerAwareMiddlewareTrait;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Controller.initialize' => 'removeSessionIdCookieIfOnJwtAuth',
        ];
    }

    /**
     * Remove all session related cookies.
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function removeSessionIdCookieIfOnJwtAuth(EventInterface $event): void
    {
        /** @var \Cake\Controller\Controller $controller */
        $controller = $event->getSubject();
        $response = $controller->getResponse();
        $request = $controller->getRequest();
        $service = new JwtRequestDetectionService($request);
        if ($service->useJwtAuthentication()) {
            $sessionCookie = Configure::read('Session.cookie', session_name());
            if (is_string($sessionCookie)) {
                $response = $response->withExpiredCookie(new Cookie($sessionCookie));
            }
            $controller->setResponse($response);
        }
    }
}
