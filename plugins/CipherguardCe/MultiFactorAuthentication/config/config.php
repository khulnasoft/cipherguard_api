<?php
return [
    'cipherguard' => [
        'plugins' => [
            'multiFactorAuthentication' => [
                'version' => '1.1.0',
                'enabled' => true,
                'providers' => [
                    'totp' => filter_var(env('CIPHERGUARD_PLUGINS_MFA_PROVIDERS_TOTP', false), FILTER_VALIDATE_BOOLEAN),
                    'duo' => filter_var(env('CIPHERGUARD_PLUGINS_MFA_PROVIDERS_DUO', false), FILTER_VALIDATE_BOOLEAN),
                    'yubikey' => filter_var(env('CIPHERGUARD_PLUGINS_MFA_PROVIDERS_YUBIKEY', false), FILTER_VALIDATE_BOOLEAN), //phpcs:ignore
                ],
                'totp' => [
                    'secretLength' => env('CIPHERGUARD_PLUGINS_MFA_TOTP_SECRET_LENGTH', null),
                ],
                'yubikey' => [
                    'clientId' => env('CIPHERGUARD_PLUGINS_MFA_YUBIKEY_CLIENTID', null),
                    'secretKey' => env('CIPHERGUARD_PLUGINS_MFA_YUBIKEY_SECRETKEY', null),
                ],
                'duo' => [
                    // @deprecated since v3.10 with Duo v4 support: CIPHERGUARD_PLUGINS_MFA_DUO_INTEGRATIONKEY, CIPHERGUARD_PLUGINS_MFA_DUO_SECRETKEY, CIPHERGUARD_PLUGINS_MFA_DUO_HOST
                    'clientId' => env(
                        'CIPHERGUARD_PLUGINS_MFA_DUO_CLIENT_ID',
                        env('CIPHERGUARD_PLUGINS_MFA_DUO_INTEGRATIONKEY', null)
                    ),
                    'clientSecret' => env(
                        'CIPHERGUARD_PLUGINS_MFA_DUO_CLIENT_SECRET',
                        env('CIPHERGUARD_PLUGINS_MFA_DUO_SECRETKEY', null)
                    ),
                    'apiHostName' => env(
                        'CIPHERGUARD_PLUGINS_MFA_DUO_API_HOSTNAME',
                        env('CIPHERGUARD_PLUGINS_MFA_DUO_HOST', null)
                    ),
                ],
                'sortProvidersByLastUsage' => filter_var(env('CIPHERGUARD_PLUGINS_MFA_SORT_PROVIDERS_BY_LAST_USAGE', true), FILTER_VALIDATE_BOOLEAN), //phpcs:ignore
            ],
        ],
    ],
];
