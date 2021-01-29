<?php

namespace App\Spreadsheet;

use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Ohffs\SimpleSpout\ExcelSheet;

class Spreadsheet
{
    public function import($filename)
    {
        return (new ExcelSheet)->import($filename);
        // $reader = ReaderFactory::create(Type::XLSX);
        // $reader->open($filename);
        // $rows = [];
        // foreach ($reader->getSheetIterator() as $sheet) {
        //     foreach ($sheet->getRowIterator() as $row) {
        //         $rows[] = $row;
        //     }
        // }
        // $reader->close();

        // return $rows;
    }

    public function generate($data, $filename = null)
    {
        return (new ExcelSheet)->generate($data);
        // if (! $filename) {
        //     $filename = tempnam('/tmp', 'ASM');
        // }
        // $writer = WriterFactory::create(Type::XLSX);
        // $writer->openToFile($filename);
        // $writer->addRows($data);
        // $writer->close();

        // return $filename;
    }
}
