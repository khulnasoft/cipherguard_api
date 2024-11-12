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
 * @since         2.10.0
 */
namespace Cipherguard\EmailNotificationSettings\Form;

use Cake\Event\EventManager;
use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettings;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionInterface;
use Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionRegisterEvent;

class EmailNotificationSettingsForm extends Form
{
    /**
     * @var \Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionInterface[]
     */
    private $notificationSettingsDefinitions = [];

    /**
     * @param \Cake\Event\EventManager|null $eventManager An instance of event manager
     */
    public function __construct(?EventManager $eventManager = null)
    {
        parent::__construct($eventManager);

        $this->getEventManager()->dispatch(EmailNotificationSettingsDefinitionRegisterEvent::create($this));
    }

    /**
     * @param \Cipherguard\EmailNotificationSettings\Utility\EmailNotificationSettingsDefinitionInterface $definition def
     * @return void
     */
    public function addEmailNotificationSettingsDefinition(EmailNotificationSettingsDefinitionInterface $definition)
    {
        $this->notificationSettingsDefinitions[] = $definition;
    }

    /**
     * Database configuration schema. Build schema from all notification settings definitions schemas.
     *
     * @param \Cake\Form\Schema $schema schema
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        foreach ($this->notificationSettingsDefinitions as $notificationSettingsDefinition) {
            $notificationSettingsDefinition->buildSchema($schema);
        }

        return $schema;
    }

    /**
     * Validation rules. Build validator rules from all notification settings definitions validators.
     *
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        foreach ($this->notificationSettingsDefinitions as $notificationSettingsDefinition) {
            $notificationSettingsDefinition->buildValidator($validator);
        }

        return $validator;
    }

    /**
     * Transform form data into the expected org settings format
     *
     * @param array $data The form data
     * @return array $settings The org settings data
     */
    public static function formatFormDataToOrgSettings(?array $data = []): array
    {
        if (count($data) === 0) {
            return $data;
        }

        $settings = [];
        $data = static::stripInvalidKeys($data);

        foreach ($data as $prop => $propVal) {
            $key = EmailNotificationSettings::underscoreToDottedFormat($prop);
            $settings[$key] = $propVal;
        }

        return $settings;
    }

    /**
     * Strip invalid email notification setting keys from the given $data array
     *
     * @param array $data The data array
     * @return array array with the invalid keys removed
     */
    public static function stripInvalidKeys(array $data): array
    {
        if (empty($data)) {
            return $data;
        }

        foreach ($data as $prop => $propVal) {
            if (!EmailNotificationSettings::isConfigKeyValid($prop)) {
                unset($data[$prop]);
            }
        }

        return $data;
    }
}
