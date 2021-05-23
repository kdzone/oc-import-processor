<?php namespace Kdz1\Import;

use Yaml;

/**
 * Class ImportFromXML
 */
class ImportFromXML
{

    protected $obProcessor;

    /**
     * ImportFromXML constructor.
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
        $xdoc = simplexml_load_file($obProcessor->dataFile);

        $arConfig = $obProcessor->getConfig();

        $xpath = array_get($arConfig, 'list.xpath');
        //if (empty($xpath)) error

        $arFields = array_get($arConfig, 'fields');
        //if (empty($fields)) error

        $arList = $xdoc->xpath($xpath);
        //if (empty($list)) error

//        $output = $obProcessor->output;
        foreach ($arList as $node) {
            $arRec = $this->getRecValues($arFields, $node);
            $obProcessor->importRec($arRec);
            $obProcessor->incProcessedCount();
            if ($obProcessor->getCancel()) {
                break;
            }
        }

//        $output->writeln('== ' . $this->obProcessor->dataFile);
//        $output->writeln('== count ' . count($arList));
    }

    /**
     * @param array $arFields
     * @param \SimpleXMLElement $node
     * @return array
     */
    private function getRecValues(array $arFields, \SimpleXMLElement $node)
    {
        $arData = [];
        foreach ($arFields as $fn => $fobj) {
            $arData[$fn] = $this->getFldValue($node, $fobj);
        }
        return $arData;
    }


    /**
     * @param \SimpleXMLElement $node
     * @param $fobj
     * @return string|null
     */
    private function getFldValue(\SimpleXMLElement $node, $fobj)
    {
        $xpath = array_get($fobj, 'xpath');
        if ($xpath) {
            return $this->getXmlValue($node, $xpath);
        }

        return array_get($fobj, 'value', null);
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $xpath
     * @return string|null
     */
    private function getXmlValue(\SimpleXMLElement $node, string $xpath)
    {
        $arNodeList = $node->xpath($xpath);
        return !empty($arNodeList) && (count($arNodeList) > 0) ? (string)$arNodeList[0] : null;
    }

}
