<?php

namespace App\Exports;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams the applications currently in view (active filters included) to CSV
 * or XLSX. The filter query lives on the model, so an export always matches
 * the table the administrator is looking at.
 */
class ApplicationExporter
{
    public const COLUMNS = [
        'Reference Number', 'Applicant Name', 'Email', 'Phone', 'Position',
        'State', 'Qualification', 'Gender', 'Status', 'Date Submitted',
    ];

    public function download(Request $request, string $format): StreamedResponse
    {
        $applications = Application::visibleTo($request->user())
            ->filtered($request)
            ->orderByDesc('submitted_at')
            ->select([
                'id', 'reference', 'first_name', 'middle_name', 'last_name', 'email', 'phone',
                'position_id', 'residence_state_id', 'highest_qualification', 'gender', 'status', 'submitted_at',
            ])
            ->lazy(500);

        $extension = $format === 'xlsx' ? 'xlsx' : 'csv';
        $contentType = $extension === 'xlsx'
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'text/csv; charset=UTF-8';

        return Response::streamDownload(function () use ($applications, $extension) {
            $writer = $extension === 'xlsx' ? new XlsxWriter : new CsvWriter;
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues(self::COLUMNS));

            foreach ($applications as $application) {
                $writer->addRow(Row::fromValues($this->row($application)));
            }

            $writer->close();
        }, 'applications-'.now()->format('Ymd-His').'.'.$extension, ['Content-Type' => $contentType]);
    }

    /** @return list<string> */
    private function row(Application $application): array
    {
        return array_map(fn (string $value): string => $this->escapeSpreadsheetFormula($value), [
            $application->reference,
            $application->full_name,
            $application->email,
            $application->phone,
            $application->position?->title ?? '',
            $application->residenceState?->name ?? '',
            $application->highest_qualification,
            ucfirst($application->gender),
            $application->statusEnum()->label(),
            $application->submitted_at?->format('d M Y H:i') ?? '',
        ]);
    }

    private function escapeSpreadsheetFormula(string $value): string
    {
        return preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1
            ? "'".$value
            : $value;
    }
}
