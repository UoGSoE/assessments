<?php

namespace App\Spreadsheet;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

class Spreadsheet
{
    public function import($filename)
    {
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($filename);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = trim($row);
            }
        }
        $reader->close();
        return $rows;
    }

    public function generate($data, $filename = null)
    {
        if (!$filename) {
            $filename = tempnam('/tmp', 'ASM');
        }
        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($filename);
        $writer->addRows($data);
        $writer->close();
        return $filename;
    }
}
