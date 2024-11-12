<?php
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
 * @since         4.3.0
 */
// @codingStandardsIgnoreStart
use Migrations\AbstractMigration;

class V430AddUserDisabledField extends AbstractMigration
{
    /**
     * Up
     *
     * @return void
     */
    public function up()
    {
        $this->table('users')
            ->addColumn('disabled', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'after' => 'deleted',
            ])
            ->save();
    }
}
// @codingStandardsIgnoreEnd
