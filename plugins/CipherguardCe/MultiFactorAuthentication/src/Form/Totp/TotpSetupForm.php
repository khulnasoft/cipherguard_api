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
 * @since         2.4.0
 */
namespace Cipherguard\MultiFactorAuthentication\Form\Totp;

use App\Error\Exception\ValidationException;
use Cake\Form\Schema;
use Cake\Http\Exception\InternalErrorException;
use Cake\Validation\Validator;
use OTPHP\Factory;
use Cipherguard\MultiFactorAuthentication\Form\MfaForm;
use Cipherguard\MultiFactorAuthentication\Utility\MfaAccountSettings;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class TotpSetupForm extends MfaForm
{
    /**
     * @var \OTPHP\OTPInterface|\OTPHP\HOTPInterface
     */
    protected $otp;

    /**
     * Build form schema
     *
     * @param \Cake\Form\Schema $schema schema
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): \Cake\Form\Schema
    {
        return $schema
            ->addField('otpProvisioningUri', ['type' => 'string'])
            ->addField('totp', ['type' => 'string']);
    }

    /**
     * Build form validation
     *
     * @param \Cake\Validation\Validator $validator validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('otpProvisioningUri')
            ->notEmptyString('otpProvisioningUri')
            ->add('otpProvisioningUri', ['isValidOtpProvisioningUri' => [
                'rule' => [$this, 'isValidOtpProvisioningUri'],
                'message' => __('This OTP provision uri is not valid.'),
            ]]);

        $validator
            ->requirePresence('totp', __('An OTP is required.'))
            ->notEmptyString('totp', __('The OTP should not be empty.'))
            ->add('totp', ['isValidOtp' => [
                'rule' => [$this, 'isValidOtp'],
                'message' => __('This OTP is not valid.'),
            ]]);

        return $validator;
    }

    /**
     * Custom validation rule to validate otp provisioning uri
     *
     * @param string $value otp provisioning uri
     * @return bool
     */
    public function isValidOtpProvisioningUri(string $value)
    {
        try {
            $this->otp = Factory::loadFromProvisioningUri($value);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        return true;
    }

    /**
     * Custom validation rule to validate otp provisioning uri
     *
     * @param string $value otp provisioning uri
     * @return bool
     */
    public function isValidOtp(string $value)
    {
        if (!is_object($this->otp) || !is_numeric($value)) {
            return false;
        }

        return $this->otp->verify($value);
    }

    /**
     * Form post validation treatment
     *
     * @param array $data user submited data
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        try {
            $data = ['otpProvisioningUri' => $data['otpProvisioningUri']];
            MfaAccountSettings::enableProvider($this->uac, MfaSettings::PROVIDER_TOTP, $data);
        } catch (ValidationException $e) {
            throw new InternalErrorException('Could not save the OTP settings. Please try again later.', 500, $e);
        }

        return true;
    }
}
