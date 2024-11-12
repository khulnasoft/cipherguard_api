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
 * @since         3.11.0
 */

return [
    // Additional log engines
    'Log' => [
        'actionLogsOnFile' => [
            'enabled' => filter_var(env('LOG_ACTION_LOGS_ON_FILE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
            'className' => \Cake\Log\Engine\FileLog::class,
            'formatter' => env('LOG_ACTION_LOGS_ON_FILE_FORMATTER', 'Cake\Log\Formatter\DefaultFormatter'),
            'strategy' => env('LOG_ACTION_LOGS_ON_FILE_STRATEGY', 'Cipherguard\Log\Strategy\ActionLogsDefaultQueryStrategy'),
            'scopes' => ['actionLogs'],
            'path' => env('LOG_ACTION_LOGS_ON_FILE_PATH', LOGS),
            'file' => env('LOG_ACTION_LOGS_ON_FILE_FILE', 'action-logs'),
            'url' => env('LOG_ACTION_LOGS_ON_FILE_URL'),
        ],
        'actionLogsOnSyslog' => [
            'enabled' => filter_var(env('LOG_ACTION_LOGS_ON_SYSLOG_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
            'className' => \Cake\Log\Engine\SyslogLog::class,
            'formatter' => env('LOG_ACTION_LOGS_ON_SYSLOG_FORMATTER', 'Cake\Log\Formatter\DefaultFormatter'),
            'strategy' => env('LOG_ACTION_LOGS_ON_SYSLOG_STRATEGY', 'Cipherguard\Log\Strategy\ActionLogsDefaultQueryStrategy'),
            'scopes' => ['actionLogs'],
            'flag' => env('LOG_ACTION_LOGS_ON_SYSLOG_FLAG', LOG_ODELAY),
            'prefix' => env('LOG_ACTION_LOGS_ON_SYSLOG_PREFIX', ''),
            'facility' => env('LOG_ACTION_LOGS_ON_SYSLOG_FACILITY', LOG_USER),
        ],
    ],
];
