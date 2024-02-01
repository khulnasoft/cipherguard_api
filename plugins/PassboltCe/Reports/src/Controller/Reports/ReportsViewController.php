<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         2.13.0
 */
namespace Cipherguard\Reports\Controller\Reports;

use App\Controller\AppController;
use App\Model\Entity\Role;
use App\Model\Table\Dto\FindIndexOptions;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use InvalidArgumentException;
use Cipherguard\Reports\Service\ReportViewService;
use Cipherguard\Reports\Utility\ReportInterface;

/**
 * ReportsViewController Class
 */
class ReportsViewController extends AppController
{
    public const DEFAULT_LAYOUT = 'Cipherguard/Reports.Reports/ReportsLayout';

    /**
     * @var \Cipherguard\Reports\Service\ReportViewService
     */
    private $reportViewService;

    /**
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->reportViewService = new ReportViewService();
        $this->Users = $this->fetchTable('Users');
    }

    /**
     * @param string $reportSlug Slug of the report to retrieve
     * @throws \Exception
     * @throws \Cake\Http\Exception\BadRequestException If the requested report does not exist
     * @return void
     */
    public function view(string $reportSlug)
    {
        if ($this->User->role() !== Role::ADMIN) {
            throw new ForbiddenException(__('Only administrators can view reports.'));
        }

        // Retrieve the report argument passed as url parameters.
        $arguments = func_get_args();
        $reportArguments = array_slice($arguments, 1);

        try {
            $report = $this->reportViewService->getReport($reportSlug, $reportArguments);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestException(__('The requested report `{0}` does not exist.', $reportSlug));
        }

        $options = $this->formatRequestData($report->getSupportedOptions());

        $creator = $this->Users->get($this->User->id(), ['contain' => 'Profiles']);

        $report
            ->setOptions($options)
            ->setCreator($creator);

        if (!$this->request->is('json')) {
            $this->renderReportInHtml($report);
        } else {
            $this->success(__('The operation was successful.'), $report);
        }
    }

    /**
     * Format request data
     * Get supported options from report service and extract / validate them using QueryString component
     *
     * @param \App\Model\Table\Dto\FindIndexOptions $supportedOptions allowed options
     * @return \App\Model\Table\Dto\FindIndexOptions
     */
    private function formatRequestData(FindIndexOptions $supportedOptions)
    {
        $allowedOptions = $supportedOptions->getAllowedOptions();
        $validators = $supportedOptions->getFilterValidators();
        $query = $this->QueryString->get($allowedOptions, $validators);

        return FindIndexOptions::createFromArray($query);
    }

    /**
     * Set view variables, theme and template for html render
     *
     * @param \Cipherguard\Reports\Utility\ReportInterface $report Instance of Report to render
     * @return void
     */
    private function renderReportInHtml(ReportInterface $report)
    {
        $this->viewBuilder()
            ->setTemplatePath('Reports/html')
            ->setLayout(static::DEFAULT_LAYOUT)
            ->setTheme(null)
            ->setTemplate($report->getTemplate());
        $this->set('report', $report->createReport());
    }
}
