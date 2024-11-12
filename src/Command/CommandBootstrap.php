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
namespace App\Command;

use App\Model\Entity\Role;
use App\Utility\UserAccessControl;
use App\Utility\UserAction;

/**
 * App Shell Bootstrap
 *
 * Bootstrap for AppShell used as a singleton.
 * Since an AppShell can be initialized inside an AppShell (see ldap plugin),
 * this is done to avoid cascading effects on Event listeners
 * being added several times.
 */
class CommandBootstrap
{
    /**
     * Instance of class used for singleton.
     *
     * @var \App\Command\CommandBootstrap|null
     */
    private static $instance = null;

    /**
     * Init function.
     *
     * @return self
     */
    public static function init(): CommandBootstrap
    {
        if (!isset(self::$instance)) {
            self::$instance = new CommandBootstrap();
            self::$instance->_initUserAction();
        }

        return self::$instance;
    }

    /**
     * Init the UserAction component if it's not already initialized.
     * This is to avoid errors while executing tasks that don't implement UserAction.
     *
     * @return void
     */
    private function _initUserAction()
    {
        // Context will look like the example below:
        // CMD cipherguard install --no-admin
        $args = $_SERVER['argv'];
        $args[0] = 'CMD';
        $context = implode(' ', $args);

        try {
            UserAction::getInstance();
        } catch (\Exception $e) {
            $uac = new UserAccessControl(Role::GUEST, null);
            UserAction::getInstance($uac, 'shell', $context);
        }
    }
}
