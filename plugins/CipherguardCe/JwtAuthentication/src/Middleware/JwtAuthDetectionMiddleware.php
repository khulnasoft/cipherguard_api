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
namespace Cipherguard\JwtAuthentication\Middleware;

use App\Authenticator\SessionIdentificationServiceInterface;
use App\Middleware\ContainerAwareMiddlewareTrait;
use Authentication\AuthenticationServiceInterface;
use Cake\Core\ContainerInterface;
use Cipherguard\JwtAuthentication\Authenticator\JwtSessionIdentificationService;
use Cipherguard\JwtAuthentication\Service\Middleware\JwtAuthenticationService;
use Cipherguard\JwtAuthentication\Service\Middleware\JwtRequestDetectionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtAuthDetectionMiddleware implements MiddlewareInterface
{
    use ContainerAwareMiddlewareTrait;

    /**
     * Informs the request if JWT Authentication is detected.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The handler.
     * @return \Psr\Http\Message\ResponseInterface The response.
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $usesJWTAuthentication = (new JwtRequestDetectionService($request))->useJwtAuthentication();

        if ($usesJWTAuthentication) {
            $this->services($this->getContainer($request));
        }

        /** @var \Cake\Http\ServerRequest $request */
        $request = $request->withAttribute(
            JwtRequestDetectionService::IS_JWT_AUTH_REQUEST,
            $usesJWTAuthentication
        );

        return $handler->handle($request);
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        $container
            ->extend(AuthenticationServiceInterface::class)
            ->setConcrete(JwtAuthenticationService::class);

        $container
            ->extend(SessionIdentificationServiceInterface::class)
            ->setConcrete(JwtSessionIdentificationService::class);
    }
}
