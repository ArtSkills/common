<?php
declare(strict_types=1);

namespace ArtSkills\Excel\Format;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Box\Spout\Reader\ReaderAbstract;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Reader\SheetInterface;

class SpoutReaderFormat extends AbstractReaderFormat
{
    /** Кол-во пустых строк после которых прекращается парсинг файла */
    private const MAX_EMPTY_ROWS = 10;

    /**
     * @var ReaderInterface|ReaderAbstract
     */
    private ReaderInterface $_spreadsheet;

    /**
     * Constructor.
     *
     * @param string $fileName
     * @throws UnsupportedTypeException
     * @throws IOException
     */
    public function __construct(string $fileName)
    {
        $this->_spreadsheet = ReaderEntityFactory::createReaderFromFile($fileName);
        $this->_spreadsheet->setShouldPreserveEmptyRows(true); // @phpstan-ignore-line
        $this->_spreadsheet->open($fileName);
    }

    /**
     * @inheritDoc
     * @throws ReaderNotOpenedException
     */
    public function getCell(string $pCoordinate, int $page = 1)
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

            if ($emptyStrCount > self::MAX_EMPTY_ROWS) {
                break;
            }

            $cells = $row->getCells();

            $value = '';
            $rowCells = [];
            foreach ($cells as $cell) {
                $rowCells[] = $cell->getValue();

                try {
                    $value .= $cell->getValue();
                } catch (\Throwable $exception) {
                    $value.= 'something'; // всё равно, что лежит в этой ячейке, шлавное что заполнена
                }
            }

            if ($value === '') {
                $emptyStrCount++;

                if ($skipEmptyRows) {
                    continue;
                }
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

        /** @var SheetInterface $sheet */
        foreach ($sheetIterator as $sheet) {
            if ($sheetIterator->key() === $page) {
                return $sheet;
            }
        }

        return null;
    }
}
