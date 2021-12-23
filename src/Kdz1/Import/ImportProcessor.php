<?php namespace Kdz1\Import;

/**
 * Class ImportProcessor
 */
abstract class ImportProcessor
{

    const DATA_FORMAT_XML = 'xml';
    const DATA_FORMAT_CSV = 'csv';

    protected $arConfig = [];

    protected $bCancel = false;
    protected $iProcessedCount = 0;
    protected $iCreatedCount = 0;
    protected $iUpdatedCount = 0;

    public $dataFormat = self::DATA_FORMAT_XML;
    public $dataFile = null;

    public $progressBar = null;
    public $output = null;

    /**
     * ImportProcessor constructor.
     * @param array $arConfig
     */
    public function __construct(array $arConfig)
    {
        $this->arConfig = $arConfig;
    }

    /**
     * Get created count
     * @return array
     */
    public function getConfig()
    {
        return $this->arConfig;
    }

    /**
     * @return bool
     */
    public function getCancel()
    {
        return $this->bCancel;
    }

    /**
     * Get processed count
     * @return int
     */
    public function getProcessedCount()
    {
        return $this->iProcessedCount;
    }

    /**
     *
     */
    public function incProcessedCount(): void
    {
        $this->iProcessedCount++;

        if (!empty($this->progressBar)) {
            $this->progressBar->advance();
        }
    }

    /**
     * Get created count
     * @return int
     */
    public function getCreatedCount()
    {
        return $this->iCreatedCount;
    }

    /**
     *
     */
    public function incCreatedCount(): void
    {
        $this->iCreatedCount++;
    }

    /**
     * Get updated count
     * @return int
     */
    public function getUpdatedCount()
    {
        return $this->iUpdatedCount;
    }

    /**
     *
     */
    public function incUpdatedCount(): void
    {
        $this->iUpdatedCount++;
    }

    /**
     *
     */
    public function run(): void
    {
        $this->iProcessedCount = 0;
        $this->iCreatedCount = 0;
        $this->iUpdatedCount = 0;
        $this->bCancel = false;
        switch ($this->dataFormat):
            case self::DATA_FORMAT_CSV:
                $obImport = new ImportFromCSV($this);
                break;
            default:
                $obImport = new ImportFromXML($this);
        endswitch;

        $obImport->run();

        if (!empty($this->progressBar)) {
            $this->progressBar->finish();
        }
    }

    /**
     * @param $arValues
     */
    abstract public function importRec($arValues): void;

}
