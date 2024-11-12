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
 * @since         2.0.0
 */
namespace App\Controller\Healthcheck;

use App\Controller\AppController;

class HealthcheckStatusController extends AppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->Authentication->allowUnauthenticated(['status']);

        return parent::beforeFilter($event);
    }

    /**
     * A lightweight method that returns OK
     * Useful to know if the site is up or not
     *
     * @return void
     */
    public function status()
    {
        $this->viewBuilder()
            ->setLayout('ajax')
            ->setTemplatePath('Healthcheck');
        $this->success(__('OK'), 'OK');
    }
}
