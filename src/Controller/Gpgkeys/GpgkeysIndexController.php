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
namespace App\Controller\Gpgkeys;

use App\Controller\AppController;

/**
 * GpgkeysIndexController Class
 */
class GpgkeysIndexController extends AppController
{
    /**
     * @var \App\Model\Table\GpgkeysTable
     */
    protected $Gpgkeys;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('ApiPagination', [
            'model' => 'Gpgkeys',
        ]);
        $this->Gpgkeys = $this->fetchTable('Gpgkeys');
    }

    public $paginate = [
        'sortableFields' => [
            'Gpgkeys.key_id',
        ],
    ];

    /**
     * Gpgkey Index action
     *
     * @return void
     */
    public function index()
    {
        $this->assertJson();

        $whitelist = ['filter' => ['modified-after', 'is-deleted', 'has-users']];
        $options = $this->QueryString->get($whitelist);
        $gpgkeys = $this->Gpgkeys->find('index', $options);
        $this->paginate($gpgkeys);
        $this->success(__('The operation was successful.'), $gpgkeys);
    }
}
