<?php

namespace App\Services;

use App\Models\TreatedSalary;
use App\Settings\AppSettings;
use TCPDF;

class PdfGenerateService extends TCPDF
{
    // Define consistent color scheme
    private array $blackColor = [0, 0, 0];
    private array $whiteColor = [255, 255, 255];
    private array $lightGrayColor = [245, 245, 245];

    // Define consistent layout constants
    private const PAGE_MARGIN =15;
    private const CONTENT_PADDING = 3;
    private const SECTION_SPACING = 4;
    private const ROW_HEIGHT = 6;
    private const HEADER_HEIGHT = 7;
    private const BOX_PADDING = 2;

    // Define consistent column widths for tables
    private const COL_NUMBER = 18;
    private const COL_DESIGNATION = 90;
    private const COL_BASE = 28;
    private const COL_RATE = 22;
    private const COL_RETAINED = 28;
    private const COL_GROSS = 35;

    private int $rowCounter = 0;

    public function __construct(public AppSettings $settings)
    {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->initializeDocument();
    }

    private function initializeDocument(): void
    {
        $this->SetCreator($this->settings->name);
        $this->SetAuthor($this->settings->name);
        $this->SetMargins(self::PAGE_MARGIN, self::PAGE_MARGIN, self::PAGE_MARGIN);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetAutoPageBreak(true, self::PAGE_MARGIN + 5);
        $this->SetFont('helvetica', '', 9);
    }

    public function generateSalarySlip(TreatedSalary $salary): void
    {
        $config = config('salary_slip');
        $employee = $salary->employee;

        $this->rowCounter = 0;
        $this->SetTitle($config['labels']['pay_slip']);
        $this->SetSubject($config['labels']['pay_slip']);
        $this->AddPage();

        $this->renderHeader($config);
        $this->renderPeriodInfo($salary, $config);
        $this->renderEmployeeInfo($employee, $salary, $config);
        $this->renderWorkingHours($salary, $config);
        $this->renderBonusSection($salary, $config);
        $this->renderDeductionSection($salary, $config);
        $this->renderRecoveriesSection($salary, $config);
        $this->renderSummary($salary, $config);
        $this->renderFooter();

        $this->Output('bulletin_de_paie.pdf', 'I');
    }

    private function renderHeader(array $config): void
    {
        // Company information positioned at the top right
        $this->renderCompanyInfo();

        // Document title with underline
        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY(self::PAGE_MARGIN, 25);
        $this->Cell(0, 8, $config['labels']['pay_slip'], 0, 1, 'L');

        // Add underline for title
        $this->SetDrawColor(...$this->blackColor);
        $titleWidth = $this->GetStringWidth($config['labels']['pay_slip']);
        $this->Line(self::PAGE_MARGIN, 33, self::PAGE_MARGIN + $titleWidth, 33);

        $this->SetY(38);
    }

    private function renderCompanyInfo(): void
    {
        $this->SetTextColor(...$this->blackColor);

        // Company name (top right, bold)
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY(120, 8);
        $this->Cell(0, 5, $this->settings->name, 0, 1, 'R');

        // Address (smaller font)
        $this->SetFont('helvetica', '', 9);
        $this->SetXY(120, 14);
        $this->Cell(0, 4, $this->settings->address, 0, 1, 'R');

        // Phone number
        $this->SetXY(120, 18);
        $this->Cell(0, 4, 'TEL: ' . $this->settings->phone, 0, 1, 'R');
    }

    private function renderPeriodInfo(TreatedSalary $salary, array $config): void
    {
        $contentWidth = $this->getPageWidth() - (2 * self::PAGE_MARGIN);
        $boxHeight = 20;

        // Draw border for period info box
        $this->SetDrawColor(...$this->blackColor);
        $this->Rect(self::PAGE_MARGIN, $this->GetY(), $contentWidth, $boxHeight);

        $startY = $this->GetY();
        $this->SetY($startY + self::BOX_PADDING);

        // Period information with proper spacing
        $this->SetFont('helvetica', 'B', 9);
        $this->SetX(self::PAGE_MARGIN + self::CONTENT_PADDING);
        $this->Cell(50, 5, $config['labels']['period'] . ' :', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, $salary->start_date->format('d/m/Y') . ' au ' . $salary->end_date->format('d/m/Y'), 0, 1, 'L');

        $this->SetFont('helvetica', 'B', 9);
        $this->SetX(self::PAGE_MARGIN + self::CONTENT_PADDING);
        $this->Cell(50, 5, $config['labels']['payment_date'] . ' :', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, $salary->treatment_date->format('d/m/Y'), 0, 1, 'L');

        $this->SetFont('helvetica', 'B', 9);
        $this->SetX(self::PAGE_MARGIN + self::CONTENT_PADDING);
        $this->Cell(50, 5, $config['labels']['payment_mode'] . ' :', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 5, 'VIREMENT BANCAIRE', 0, 1, 'L');

        $this->SetY($startY + $boxHeight + self::SECTION_SPACING);
    }

    private function renderEmployeeInfo($employee, TreatedSalary $salary, array $config): void
    {
        $this->addSectionHeader($config['labels']['employee']);

        $contentWidth = $this->getPageWidth() - (2 * self::PAGE_MARGIN);
        $columnWidth = ($contentWidth - 10) / 2;
        $boxHeight = 18;

        // Draw two-column layout with borders
        $this->SetDrawColor(...$this->blackColor);
        $leftX = self::PAGE_MARGIN;
        $rightX = self::PAGE_MARGIN + $columnWidth + 10;

        $startY = $this->GetY();
        $this->Rect($leftX, $startY, $columnWidth, $boxHeight);
        $this->Rect($rightX, $startY, $columnWidth, $boxHeight);

        // Left column content
        $this->SetXY($leftX + self::CONTENT_PADDING, $startY + self::BOX_PADDING);
        $this->addInfoRowInBox('Nom:', $employee->name);
        $this->SetX($leftX + self::CONTENT_PADDING);
        $this->addInfoRowInBox($config['labels']['department'] . ':', $employee->department->name ?? '');

        // Right column content
        $this->SetXY($rightX + self::CONTENT_PADDING, $startY + self::BOX_PADDING);
        $this->addInfoRowInBox($config['labels']['base_salary'] . ':', $this->formatCurrency($salary->employee->basic_salary));
        $this->SetX($rightX + self::CONTENT_PADDING);
        $this->addInfoRowInBox($config['labels']['family_status'] . ':', $employee->marital_status->getLabel() ?? '');

        $this->SetY($startY + $boxHeight + self::SECTION_SPACING);
    }

    private function renderWorkingHours(TreatedSalary $salary, array $config): void
    {
        $this->addSectionHeader($config['labels']['working_hours']);

        $contentWidth = $this->getPageWidth() - (2 * self::PAGE_MARGIN);
        $boxHeight = 16;

        // Draw border for working hours box
        $this->SetDrawColor(...$this->blackColor);
        $startY = $this->GetY();
        $this->Rect(self::PAGE_MARGIN, $startY, $contentWidth, $boxHeight);

        $this->SetXY(self::PAGE_MARGIN + self::CONTENT_PADDING, $startY + self::BOX_PADDING);

        // First row of working hours data
        $this->renderWorkingHoursRow(
            'Jours ouvrés totaux :',
            number_format($salary->total_working_days, 2, ',', ' '),
            'Jours ouvrés effectués :',
            number_format($salary->actual_working_days, 2, ',', ' ')
        );

        // Second row of working hours data
        $this->SetX(self::PAGE_MARGIN + self::CONTENT_PADDING);
        $this->renderWorkingHoursRow(
            $config['labels']['total_working_hours'] . ' :',
            number_format($salary->total_working_hours, 2, ',', ' '),
            $config['labels']['actual_working_hours'] . ' :',
            number_format($salary->actual_working_hours, 2, ',', ' ')
        );

        $this->SetY($startY + $boxHeight + self::SECTION_SPACING);
    }

    private function renderWorkingHoursRow(string $label1, string $value1, string $label2, string $value2): void
    {
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(65, 6, $label1, 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(35, 6, $value1, 0, 0, 'L');

        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(65, 6, $label2, 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 6, $value2, 0, 1, 'L');
    }

    private function renderBonusSection(TreatedSalary $salary, array $config): void
    {
        if (empty($salary->bonus_details)) {
            return;
        }

        $this->addSectionHeader('PRIMES ET INDEMNITÉS');

        // Calculate full table width to match document width
        $contentWidth = $this->getPageWidth() - (2 * self::PAGE_MARGIN);
        $bonusTableCols = [
            'number' => 20,
            'designation' => $contentWidth - 75, // Takes remaining space
            'gross' => 55
        ];

        $this->renderBonusTableHeaderFullWidth($bonusTableCols);

        $this->renderTableRows($salary->bonus_details, function($bonus, $rowIndex, $colors) use ($bonusTableCols) {
            $this->SetFillColor(...$colors);
            $this->SetFont('helvetica', '', 9);
            $this->SetX(self::PAGE_MARGIN);

            $this->Cell($bonusTableCols['number'], self::ROW_HEIGHT, sprintf('%04d', $this->rowCounter++), 1, 0, 'C', true);
            $this->Cell($bonusTableCols['designation'], self::ROW_HEIGHT, $bonus['name'], 1, 0, 'L', true);
            $this->Cell($bonusTableCols['gross'], self::ROW_HEIGHT, number_format($bonus['amount'], 0, ',', ' '), 1, 1, 'R', true);
        });

        $this->Ln(2);
    }

    private function renderDeductionSection(TreatedSalary $salary, array $config): void
    {
        if (empty($salary->deduction_details)) {
            return;
        }

        $this->addSectionHeader('RETENUES');

        // Calculate full table width to match document width
        $contentWidth = $this->getPageWidth() - (2 * self::PAGE_MARGIN);
        $deductionTableCols = [
            'number' => 20,
            'designation' => $contentWidth - 105, // Takes remaining space
            'base' => 30,
            'rate' => 25,
            'retained' => 30
        ];

        $this->addTableHeaderFullWidth($deductionTableCols);

        $this->renderTableRows($salary->deduction_details, function($deduction, $rowIndex, $colors) use ($deductionTableCols) {
            $this->SetFillColor(...$colors);
            $this->SetFont('helvetica', '', 9);
            $this->SetX(self::PAGE_MARGIN);

            $this->Cell($deductionTableCols['number'], self::ROW_HEIGHT, sprintf('%04d', $this->rowCounter++), 1, 0, 'C', true);
            $this->Cell($deductionTableCols['designation'], self::ROW_HEIGHT, $deduction['name'], 1, 0, 'L', true);
            $this->Cell($deductionTableCols['base'], self::ROW_HEIGHT, number_format($deduction['amount'], 0, ',', ' '), 1, 0, 'R', true);
            $this->Cell($deductionTableCols['rate'], self::ROW_HEIGHT, $deduction['rate'] ?? '-', 1, 0, 'C', true);
            $this->Cell($deductionTableCols['retained'], self::ROW_HEIGHT, number_format($deduction['amount'], 0, ',', ' '), 1, 1, 'R', true);
        });

        $this->Ln(2);
    }

    private function renderRecoveriesSection(TreatedSalary $salary, array $config): void
    {
        if ($salary->total_recoveries <= 0) {
            return;
        }

        $this->addSectionHeader('RÉCUPÉRATIONS');
        $this->addTableHeader($config);

        $this->SetFillColor(...$this->whiteColor);
        $this->SetFont('helvetica', '', 9);
        $this->SetX(self::PAGE_MARGIN);

        $this->Cell(self::COL_NUMBER, self::ROW_HEIGHT, sprintf('%04d', $this->rowCounter++), 1, 0, 'C', true);
        $this->Cell(self::COL_DESIGNATION, self::ROW_HEIGHT, 'RÉCUPÉRATIONS', 1, 0, 'L', true);
        $this->Cell(self::COL_BASE, self::ROW_HEIGHT, number_format($salary->total_recoveries, 0, ',', ' '), 1, 0, 'R', true);
        $this->Cell(self::COL_RATE, self::ROW_HEIGHT, '', 1, 0, 'C', true);
        $this->Cell(self::COL_RETAINED, self::ROW_HEIGHT, number_format($salary->total_recoveries, 0, ',', ' '), 1, 1, 'R', true);
    }

    private function renderSummary(TreatedSalary $salary, array $config): void
    {
        $this->Ln(self::SECTION_SPACING);

        $contentWidth = $this->getPageWidth() - (2 * self::PAGE_MARGIN);

        // Summary calculations section
        $summaryHeight = 12;
        $this->SetDrawColor(...$this->blackColor);
        $startY = $this->GetY();
        $this->Rect(self::PAGE_MARGIN, $startY, $contentWidth, $summaryHeight);

        $this->SetXY(self::PAGE_MARGIN + self::CONTENT_PADDING, $startY + 1);

        // Total gains
        $this->renderSummaryLine($config['labels']['total_gains'] . ' :',
            $this->formatCurrency($salary->base_salary + $salary->total_bonuses));

        // Total deductions
        $this->SetX(self::PAGE_MARGIN + self::CONTENT_PADDING);
        $this->renderSummaryLine($config['labels']['total_deductions'] . ' :',
            $this->formatCurrency($salary->total_deductions));

        // Net to pay section (highlighted with border)
        $netPayY = $startY + $summaryHeight + 2;
        $netPayHeight = 10;
        $this->SetDrawColor(...$this->blackColor);
        $this->Rect(self::PAGE_MARGIN, $netPayY, $contentWidth, $netPayHeight);

        $this->SetXY(self::PAGE_MARGIN + self::CONTENT_PADDING, $netPayY + 2);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(80, 6, $config['labels']['net_to_pay'] . ' :', 0, 0, 'L');
        $this->Cell(0, 6, $this->formatCurrency($salary->final_salary), 0, 1, 'L');
    }

    private function renderFooter(): void
    {
        $this->SetY(-25);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(...$this->blackColor);
        $this->Cell(0, 4, 'Document généré le ' . date('d/m/Y'), 0, 1, 'R');
    }

    private function renderTableRows(array $items, callable $renderCallback): void
    {
        $rowColors = [$this->whiteColor, $this->lightGrayColor];

        foreach ($items as $index => $item) {
            $rowIndex = $index % 2;
            $renderCallback($item, $rowIndex, $rowColors[$rowIndex]);
        }
    }

    private function renderBonusTableHeaderFullWidth(array $bonusTableCols): void
    {
        $this->SetDrawColor(...$this->blackColor);
        $this->SetTextColor(...$this->blackColor);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetX(self::PAGE_MARGIN);

        $this->Cell($bonusTableCols['number'], self::HEADER_HEIGHT, 'N°', 1, 0, 'C', false);
        $this->Cell($bonusTableCols['designation'], self::HEADER_HEIGHT, 'Désignation', 1, 0, 'C', false);
        $this->Cell($bonusTableCols['gross'], self::HEADER_HEIGHT, 'Brut', 1, 1, 'C', false);
    }

    private function addTableHeaderFullWidth(array $deductionTableCols): void
    {
        $this->SetDrawColor(...$this->blackColor);
        $this->SetTextColor(...$this->blackColor);
        $this->SetFont('helvetica', 'B', 9);
        $this->SetX(self::PAGE_MARGIN);

        $this->Cell($deductionTableCols['number'], self::HEADER_HEIGHT, 'N°', 1, 0, 'C', false);
        $this->Cell($deductionTableCols['designation'], self::HEADER_HEIGHT, 'Désignation', 1, 0, 'C', false);
        $this->Cell($deductionTableCols['base'], self::HEADER_HEIGHT, 'Base', 1, 0, 'C', false);
        $this->Cell($deductionTableCols['rate'], self::HEADER_HEIGHT, 'Taux', 1, 0, 'C', false);
        $this->Cell($deductionTableCols['retained'], self::HEADER_HEIGHT, 'Retenue', 1, 1, 'C', false);
    }

    private function addSectionHeader(string $title): void
    {
        $this->SetDrawColor(...$this->blackColor);
        $this->SetTextColor(...$this->blackColor);
        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(1);
        $this->Cell(0, self::HEADER_HEIGHT, $title, 'B', 1, 'L', false);
    }

    private function addInfoRowInBox(string $label, string $value): void
    {
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(50, 6, $label, 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 6, $value, 0, 1, 'L');
    }

    private function renderSummaryLine(string $label, string $value): void
    {
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(80, 5, $label, 0, 0, 'L');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, $value, 0, 1, 'L');
    }

    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }
}