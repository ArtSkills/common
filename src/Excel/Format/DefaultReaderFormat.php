<?php
declare(strict_types=1);

namespace ArtSkills\Excel\Format;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\SheetInterface;
use Box\Spout\Reader\XLSX\Reader;
use Box\Spout\Reader\XLSX\Sheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Throwable;

class DefaultReaderFormat extends AbstractReaderFormat
{
    /**
     * @var Reader|Spreadsheet
     */
    private $_spreadsheet;

    /**
     * DefaultReaderFormat constructor.
     *
     * @param string $fileName
     * @throws Exception
     */
    public function __construct(string $fileName)
    {
        try {
            $this->_spreadsheet = ReaderEntityFactory::createReaderFromFile($fileName);
            $this->_spreadsheet->open($fileName);
        } catch (Throwable $exception) {
            $this->_spreadsheet = IOFactory::load($fileName);
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getCell(string $pCoordinate, int $page = 1)
    {
        if ($this->_spreadsheet instanceof Spreadsheet) {
            return $this->_getSpreadsheetCell($pCoordinate, $page);
        } else {
            return $this->_getSpoutCell($pCoordinate, $page);
        }
    }

    /**
     * Читаем содержимое ячейки через PhpSpreadsheet
     *
     * @param string $pCoordinate
     * @param int $page
     * @return \PhpOffice\PhpSpreadsheet\Cell\Cell|null
     * @throws Exception
     */
    private function _getSpreadsheetCell(string $pCoordinate, int $page): ?\PhpOffice\PhpSpreadsheet\Cell\Cell
    {
        return $this->_spreadsheet->getSheet($page - 1)->getCell($pCoordinate, false);
    }

    /**
     * Читаем содержимое ячейки через Spout\Reader
     *
     * @param string $pCoordinate
     * @param int $page
     * @return \Box\Spout\Common\Entity\Cell|null
     */
    private function _getSpoutCell(string $pCoordinate, int $page): ?\Box\Spout\Common\Entity\Cell
    {
        $sheet = $this->_getSpoutSheet($page);

        if (!$sheet) {
            return null;
        }

        $coll = '';
        $dataRowIndex = 0;
        sscanf($pCoordinate, '%[A-Z]%d', $coll, $dataRowIndex);

        $upperAlphabet = range('A', 'Z');
        $dataCollIndex = array_search($coll, $upperAlphabet, true);

        $rowIterator = $sheet->getRowIterator();
        foreach ($rowIterator as $row) {
            if ($rowIterator->key() !== $dataRowIndex) {
                continue;
            }

            return $row->getCells()[(int)$dataCollIndex] ?? null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRows(int $page = 1, int $dataRowIndex = 1, bool $skipEmptyRows = true): ?array
    {
        if ($this->_spreadsheet instanceof Spreadsheet) {
            return $this->_getSpreadsheetRows($page, $dataRowIndex, $skipEmptyRows);
        } else {
            return $this->_getSpoutRows($page, $dataRowIndex);
        }
    }

    /**
     * Читаем строки через PhpSpreadsheet
     *
     * @param int $page
     * @param int $dataRowIndex
     * @param bool $skipEmptyRows
     * @return array<int, array<int, string>>|null
     * @throws Exception
     */
    private function _getSpreadsheetRows(int $page, int $dataRowIndex, bool $skipEmptyRows): ?array
    {
        $spreadsheet = $this->_spreadsheet;

        $sheet = $spreadsheet->getSheet($page - 1);
        $sheet->garbageCollect();
        $sheetSizeArr = $sheet->getCellCollection()->getHighestRowAndColumn(); // такой костыль нужен для защиты от каких-то необъятных размеров
        $maxRow = $sheetSizeArr['row'];
        $data = $sheet->rangeToArray('A' . $dataRowIndex . ':' . $sheetSizeArr['column'] . $maxRow, null, false, false, false);

        $result = [];
        foreach ($data as $workElement) {
            $hasElementData = false;
            foreach ($workElement as $index => $xlsFieldValue) {
                if ($xlsFieldValue === "#NULL!") {
                    $workElement[$index] = $xlsFieldValue = null;
                }

                if (!$hasElementData && $xlsFieldValue !== null) {
                    $hasElementData = true;
                }

                if (!$skipEmptyRows) {
                    $hasElementData = true;
                }
            }
            if ($hasElementData) {
                $result[] = $workElement;
            }
        }
        return $result;
    }

    /**
     * Читаем строки через Spout\Reader
     *
     * @param int $page
     * @param int $dataRowIndex
     * @return array<int, array<int, string>>|null
     */
    private function _getSpoutRows(int $page, int $dataRowIndex): ?array
    {
        $sheet = $this->_getSpoutSheet($page);

        if (!$sheet) {
            return null;
        }

        $rowIterator = $sheet->getRowIterator();
        $result = [];
        $emptyStrCount = 0;
        foreach ($rowIterator as $row) {
            if ($rowIterator->key() < $dataRowIndex) {
                continue;
            }

            if ($emptyStrCount > 10) {
                break;
            }

            $cells = $row->getCells();

            $value = '';
            $rowCells = [];
            foreach ($cells as $cell) {
                $value .= $cell->getValue();
                $rowCells[] = $cell->getValue();
            }

            if ($value === '') {
                $emptyStrCount++;
                continue;
            } else {
                $emptyStrCount = 0;
            }

            $result[] = $rowCells;
        }
        return $result;
    }

    /**
     * Получение определенной страницы
     *
     * @param int $page
     * @return SheetInterface|null
     * @throws ReaderNotOpenedException
     */
    private function _getSpoutSheet(int $page): ?SheetInterface
    {
        $sheetIterator = $this->_spreadsheet->getSheetIterator();

        /** @var Sheet $sheet */
        foreach ($sheetIterator as $sheet) {
            if ($sheetIterator->key() === $page) {
                return $sheet;
            }
        }

        return null;
    }
}
