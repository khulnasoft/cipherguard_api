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

namespace Cipherguard\PasswordExpiry\Service\Settings;

use App\Error\Exception\FormValidationException;
use Cake\Http\Exception\InternalErrorException;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;

class PasswordExpiryGetSettingsService extends PasswordExpirySettingsAbstractService implements PasswordExpiryGetSettingsServiceInterface // phpcs:ignore
{
    protected ?PasswordExpirySettingsDto $dto;

    /**
     * Instantiate the dto as required by the static analysers
     */
    public function __construct()
    {
        $this->dto = null;
    }

    /**
     * Returns Password expiry settings.
     *
     * @return \Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto
     * @throws \Cake\Http\Exception\InternalErrorException When value is not an array.
     */
    final public function get(): PasswordExpirySettingsDto
    {
        if (!is_null($this->dto)) {
            return $this->dto;
        }

        /** @var \Cipherguard\PasswordExpiry\Model\Table\PasswordExpirySettingsTable $passwordExpirySettingsTable */
        $passwordExpirySettingsTable = $this->fetchTable('Cipherguard/PasswordExpiry.PasswordExpirySettings');

        /** @var \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting|null $passwordExpirySettings */
        $passwordExpirySettings = $passwordExpirySettingsTable->find()->first();

        if (is_null($passwordExpirySettings)) {
            $this->dto = $this->createDTOFromArray([]);

            return $this->dto;
        }

        if (!is_array($passwordExpirySettings->value)) {
            throw new InternalErrorException('The value should be an array');
        }

        $data = $passwordExpirySettings->value;
        $form = $this->getForm();
        if (!$form->execute($data)) {
            throw new FormValidationException(
                __('Could not validate the password expiry settings.'),
                $form
            );
        }
        $this->dto = $this->createDTOFromEntity($passwordExpirySettings, $form);

        return $this->dto;
    }
}
