<?php namespace Kdz1\Import;

/**
 * Class ImportProcessor
 */
abstract class ImportProcessor
{

    const DATA_FORMAT_XML = 'xml';
    const DATA_FORMAT_CSV = 'csv';

    protected array $arConfig = [];

    protected bool $bCancel = false;
    protected int $iProcessedCount = 0;
    protected int $iCreatedCount = 0;
    protected int $iUpdatedCount = 0;

    public string $dataFormat = self::DATA_FORMAT_XML;
    public ?string $dataFile = null;

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
    public function getConfig(): array
    {
        return $this->arConfig;
    }

    /**
     * @return bool
     */
    public function getCancel(): bool
    {
        return $this->bCancel;
    }

    /**
     * Get processed count
     * @return int
     */
    public function getProcessedCount(): int
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
    public function getCreatedCount(): int
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
    public function getUpdatedCount(): int
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
        /** @noinspection DuplicatedCode */
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

        if (!empty($this->progressBar)) {
            $this->progressBar->start(0);
        }

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
