<?php

namespace App\Services;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class GoogleSheet
{
    private $spreadsheetId;
    private $client;
    private $googleSheetService;

    public function __construct(Google_Client $client)
    {
        $this->spreadsheetId = config('gsheet.google_sheet_id');
        $this->client = new Google_Client();
        $this->client->setAuthConfig(storage_path('gsheetinventariscreds.json'));
        $this->client->addScope("https://www.googleapis.com/auth/spreadsheets");

        $this->googleSheetService = new Google_Service_Sheets($this->client);
    }

    public function readSheet(array $data)
    {
        $dimensions = $this->getDimensions($this->spreadsheetId);
        $colRange = 'Sheet1!1:1';
        $range = 'Sheet1!A1:' . $dimensions['colCount'];

        $data = $this->googleSheetService
            ->spreadsheets_values
            ->batchGet($this->spreadsheetId, ['ranges' => $range, 'majorDimension' => 'ROWS']);

        return $data->getValueRanges()[0]->values;
    }

    public function writeSheet(array $data)
    {
        $dimensions = $this->getDimensions($this->spreadsheetId);

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $data
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
        ];

        $range = "A" . ($dimensions['rowCount'] + 1);

        return $this->googleSheetService->spreadsheets_values->update($this->spreadsheetId, $range, $body, $params);
    }
    private function getDimensions($spreadSheetId)
    {
        $rowDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!A:A', 'majorDimension' => 'COLUMNS']
        );

        //if data is present at nth row, it will return array till nth row
        //if all column values are empty, it returns null
        $rowMeta = $rowDimensions->getValueRanges()[0]->values;
        if (!$rowMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        $colDimensions = $this->googleSheetService->spreadsheets_values->batchGet(
            $spreadSheetId,
            ['ranges' => 'Sheet1!1:1', 'majorDimension' => 'ROWS']
        );

        //if data is present at nth col, it will return array till nth col
        //if all column values are empty, it returns null
        $colMeta = $colDimensions->getValueRanges()[0]->values;
        if (!$colMeta) {
            return [
                'error' => true,
                'message' => 'missing row data'
            ];
        }

        return [
            'error' => false,
            'rowCount' => count($rowMeta[0]),
            'colCount' => $this->colLengthToColumnAddress(count($colMeta[0]))
        ];
    }

    public  function colLengthToColumnAddress($number)
    {
        if ($number <= 0) return null;
        $letter = '';
        while ($number > 0) {
            $temp = ($number - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $number = ($number - $temp - 1) / 26;
        }
        return $letter;
    }
}
