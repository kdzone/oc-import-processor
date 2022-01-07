<?php namespace Kdz1\Import;

use ApplicationException;
use Yaml;
use League\Csv\Reader as CsvReader;
use Backend\Behaviors\ImportExportController\TranscodeFilter;


/**
 * Class ImportFromCSV
 */
class ImportFromCSV
{

    protected $obProcessor;
    protected $arColumns = [];
    protected $arFieldDefs = [];

    /**
     * ImportFromCSV constructor.
     * @param $obProcessor
     */
    public function __construct(ImportProcessor $obProcessor)
    {
        $this->obProcessor = $obProcessor;
    }

    /**
     *
     */
    public function run()
    {
        $obProcessor = $this->obProcessor;

        // Open data
        $reader = CsvReader::createFromPath($obProcessor->dataFile, 'r');

        $arConfig = $obProcessor->getConfig();

        $options = $arConfig['options'];

        $delimeter = array_get($options, 'delimiter');
        if (!empty($delimeter)) {
            // !!! должно быть проще
            $delimeter = array_get(['\t' => "\t"], $delimeter, $delimeter);
            $reader->setDelimiter($delimeter);
        }

        $encoding = array_get($options, 'encoding');
        if (!empty($encoding) && $reader->supportsStreamFilter()) {
            $reader->addStreamFilter(sprintf(
                '%s%s:%s',
                TranscodeFilter::FILTER_NAME,
                strtolower($encoding),
                'utf-8'
            ));
        }

        $this->arFieldDefs = $arConfig['field-defs'];

        $bHeaderProcessed = false;
        $bHeader = array_get($options, 'header', true);
        foreach ($reader as $row) {

            if (!$bHeaderProcessed) {
                $arHeader = array_get($arConfig, 'csv-header', null);
                if (is_null($arHeader)) {
                    if (!$bHeader) {
                        throw new ApplicationException('Header expected!');
                    }
                    $arHeader = $row;
                }

                $this->initColumns($arHeader);

                $bHeaderProcessed = true;

                if ($bHeader) {
                    continue;
                }
            }

            // get record values
            $arRec = $this->getRecValues($row);

            // import
            $obProcessor->importRec($arRec);

            $obProcessor->incProcessedCount();

            if ($obProcessor->getCancel()) {
                break;
            }
        }
    }


    /**
     * @param array
     */
    private function initColumns($arHeader)
    {
        $this->arColumns = [];
        $count = count($arHeader);
        for ($i = 0; $i < $count; $i++) {
            $k = $arHeader[$i];
            if ($k) {
                $this->arColumns[$k] = $i;
            }
        }
    }

    /**
     * @param $row
     * @return array
     */
    private function getRecValues($row)
    {
        $arData = [];
        foreach ($this->arFieldDefs as $k => $def) {
            $v = null;

            $column = array_get($def, 'column');
            if ($column) {
                $i = $this->arColumns[$column];
                $v = $row[$i];
            } else {
                $i = array_get($this->arColumns, $k);
                if (!is_null($i)) {
                    $v = $row[$i];
                }
            }

            $script = array_get($def, 'eval');
            if ($script) {
                $v = $this->doEval($script, $this->getRowWithKeys($row));
            }

            if (is_null($v)) {
                $v = array_get($def, 'default', null);
            }

            if (!is_null($v) && ($v !== '')) {
                array_set($arData, $k, $v);
            }

        }

        return $arData;
    }

    /**
     * @param $row
     * @return array
     */
    private function getRowWithKeys(array $row)
    {
        $arr = [];
        foreach ($this->arColumns as $column => $idx) {
            $arr[$column] = $row[$idx];
        }
        return $arr;
    }

    /**
     * @param $script
     * @param $rec
     * @return mixed
     */
    private function doEval(string $script, array $row)
    {
        // !!! $row в контексте
        return eval($script);
    }

}
