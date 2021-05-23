<?php namespace Kdz1\Import;

use Yaml;
use League\Csv\Reader as CsvReader;
use Backend\Behaviors\ImportExportController\TranscodeFilter;


/**
 * Class ImportFromCSV
 */
class ImportFromCSV
{

    protected $obProcessor;
    protected $arFields = [];

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
            $reader->setDelimiter($delimeter);
        }

        $header = array_get($options, 'header', true);

        $encoding = array_get($options, 'encoding');
        if (!empty($encoding) && $reader->supportsStreamFilter()) {
            $reader->addStreamFilter(sprintf(
                '%s%s:%s',
                TranscodeFilter::FILTER_NAME,
                strtolower($encoding),
                'utf-8'
            ));
        }

        $this->initFields(array_get($options, 'fields'));

        $bHeaderProcessed = false;
        foreach ($reader as $row) {

            if ($header && !$bHeaderProcessed) {
                $map = array_get($arConfig, 'fields-map');
                if (!empty($map)) {
                    $this->initFieldsByMap($row, $map);
                    // $output->writeln(implode(';', $this->arFields));
                }
                $bHeaderProcessed = true;
                continue;
            }

            $arRec = $this->getRecValues($row);
            $obProcessor->importRec($arRec);

            $obProcessor->incProcessedCount();

            if ($obProcessor->getCancel()) {
                break;
            }
        }
        // $output->writeln('== ' . $this->obProcessor->dataFile);
        // $output->writeln('== count ' . count($arList));
    }


    /**
     * @param string|null $names
     */
    private function initFields(?string $names)
    {
        if (!empty($names)) {
            $this->arFields = explode(';', $names);
        }
    }

    /**
     * @param $arHeaderNames
     * @param $arMap
     */
    private function initFieldsByMap($arHeaderNames, $arMap)
    {
        $this->arFields = [];
        foreach ($arHeaderNames as $name) {
            $this->arFields[] = array_get($arMap, $name, '');
        }
    }

    /**
     * @param $row
     * @return array
     */
    private function getRecValues($row)
    {
        $arData = [];
        $rowCount = count($row);
        for ($i = 0; $i < $rowCount; $i++) {
            $k = $this->arFields[$i];
            if (!empty($k)) {
                $v = $row[$i];
                if ($v != '') {
                    array_set($arData, $k, $v);
                }
            }
        }
        return $arData;
    }

}
