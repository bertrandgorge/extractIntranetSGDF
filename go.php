<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/activitystream.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Get the extracts from the intranet

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
$reader->setReadDataOnly(true);
$reader->setInputEncoding('CP1252');
$reader->setDelimiter(';');
$reader->setEnclosure('"');

$outExcel = new Spreadsheet();

$extracts = glob(__dir__ . '/extracts/*.csv');
$activites = [];
$activites[] = ['Unité', 'Nom', 'Régime', 'Type', "Type d'activité", "Dates", "Heures", "Volume horaire", "Description"];

foreach ($extracts as $k => $aFile)
{
    echo $aFile . "\n";
    $annee = "Extract $k";

    $matchs = [];
    if (preg_match('@\(([0-9-]+)\)@', $aFile, $matchs))
        $annee = "Extract " . $matchs[1];

    $spreadsheet = $reader->load($aFile);
    $worksheet = $spreadsheet->getActiveSheet();

    $data = [];
    $firstCell = '';
    $previousFirstCell = '';
    $activityStream = null;

    foreach ($worksheet->getRowIterator() as $row)
    {
        $cellIterator = $row->getCellIterator();

        $row = array();
        foreach ($cellIterator as $k => $cell) {
            $row[] = $cell->getValue();
        }

        $previousFirstCell = $firstCell;
        $firstCell = reset($row);

        $data[] = $row;

        // Detect a new stream of activities:
        if ($firstCell == 'Activités')
        {
            if (!empty($activityStream))
                $activites = array_merge($activites, $activityStream->finalize());

            $activityStream = new ActivityStream($previousFirstCell);
        }

        if ($activityStream)
            $activityStream->addRow($row);
    }

    $sheet = $outExcel->createSheet();
    $sheet->setTitle($annee);
    $sheet->fromArray($data, NULL, 'A1');
}

$sheet = $outExcel->createSheet();
$sheet->setTitle('Extract normalisé');
$sheet->fromArray($activites, NULL, 'A1');

$writer = new Xlsx($outExcel);
$writer->save(__dir__ . '/out.xlsx');

