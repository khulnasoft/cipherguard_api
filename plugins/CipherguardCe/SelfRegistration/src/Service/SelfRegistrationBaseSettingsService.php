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
 * @since         3.10.0
 */
namespace Cipherguard\SelfRegistration\Service;

use App\Model\Entity\OrganizationSetting;
use Cipherguard\SelfRegistration\Form\Settings\SelfRegistrationBaseSettingsForm;
use Cipherguard\SelfRegistration\Form\Settings\SelfRegistrationEmailDomainsSettingsForm;

abstract class SelfRegistrationBaseSettingsService
{
    public const USER_SELF_REGISTRATION_SETTINGS_PROPERTY_NAME = 'selfRegistration';

    /**
     * @param array $data data in the payload
     * @return \Cipherguard\SelfRegistration\Form\Settings\SelfRegistrationBaseSettingsForm
     */
    protected function getFormFromData(array $data): SelfRegistrationBaseSettingsForm
    {
        $provider = $data['provider'] ?? null;
        switch ($provider) {
            // This is a placeholder for additional providers
            case SelfRegistrationBaseSettingsForm::SELF_REGISTRATION_EMAIL_DOMAINS:
                return new SelfRegistrationEmailDomainsSettingsForm();
            default:
                return new SelfRegistrationBaseSettingsForm();
        }
    }

    /**
     * Renders the value merging the validated settings
     * with the created/modified related fields and the id.
     *
     * The form is passed in order to ensure that the data returned is sanitized
     *
     * @param \App\Model\Entity\OrganizationSetting $setting Setting in the DB
     * @param \Cipherguard\SelfRegistration\Form\Settings\SelfRegistrationBaseSettingsForm $form Form validating the value of the setting
     * @return array
     */
    protected function getRenderedValue(OrganizationSetting $setting, SelfRegistrationBaseSettingsForm $form): array
    {
        return array_merge(
            [
                'id' => $setting->id,
            ],
            $form->getData(),
            [
                'created' => $setting->modified,
                'modified' => $setting->modified,
                'created_by' => $setting->created_by,
                'modified_by' => $setting->modified_by,
            ]
        );
    }
}
