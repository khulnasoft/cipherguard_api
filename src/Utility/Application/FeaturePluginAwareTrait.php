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
namespace App\Utility\Application;

use Cake\Core\Configure;
use Cake\Core\PluginInterface;
use Cake\Http\Exception\InternalErrorException;

trait FeaturePluginAwareTrait
{
    /**
     * @param string $name Plugin class name or plugin name, either upper case or lower case first (without the "Cipherguard/" prefix)
     * @param bool $isEnabledByDefault Should be loaded by default, if not priorly configured. False by default.
     * @return bool
     */
    public function isFeaturePluginEnabled(string $name, bool $isEnabledByDefault = false): bool
    {
        return Configure::read($this->getPluginEnabledConfigurationKey($name), $isEnabledByDefault);
    }

    /**
     * @param string $name Plugin class name or plugin name, either upper case or lower case first (without the "Cipherguard/" prefix)
     * @return void
     */
    public function enableFeaturePlugin(string $name): void
    {
        Configure::write($this->getPluginEnabledConfigurationKey($name), true);
    }

    /**
     * @param string $name Plugin class name or plugin name, either upper case or lower case first (without the "Cipherguard/" prefix)
     * @return void
     */
    public function disableFeaturePlugin(string $name): void
    {
        Configure::write($this->getPluginEnabledConfigurationKey($name), false);
    }

    /**
     * @param string $name Plugin class name or plugin name, either upper case or lower case first (without the "Cipherguard/" prefix)
     * @return string
     * @throws \Cake\Http\Exception\InternalErrorException if the plugin name is a class and not a plugin interface
     */
    protected function getPluginEnabledConfigurationKey(string $name): string
    {
        if (class_exists($name)) {
            if (!is_subclass_of($name, PluginInterface::class)) {
                throw new InternalErrorException("The class {$name} should implement PluginInterface::class.");
            }

            $extractedName = substr(substr(strrchr($name, '\\'), 1), 0, -6);
            if (empty($extractedName)) {
                throw new InternalErrorException("The class {$name} is not a valid plugin.");
            }

            $name = $extractedName;
        }

        $name = lcfirst($name);

        return "cipherguard.plugins.{$name}.enabled";
    }
}
