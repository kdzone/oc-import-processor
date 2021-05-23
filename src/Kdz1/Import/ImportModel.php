<?php namespace Kdz1\Import;

use Yaml;

/**
 * Class ImportModel
 */
class ImportModel extends ImportProcessor
{

    protected $modelClass;
    protected $key;

    public function __construct(string $configFile)
    {
        parent::__construct($configFile);
        $this->modelClass = array_get($this->getConfig(), 'modelClass');
        $this->key = array_get($this->getConfig(), 'key', 'id');
    }

    public function importRec($arValues): void
    {
        $model = $this->modelClass::where($this->key, $arValues[$this->key])->first();
        if ($model) {
            $model->fill($arValues);
            $this->incUpdatedCount();
        } else {
            $model = new $this->modelClass();
            $model->fill($arValues);
            $this->incCreatedCount();
        }
        $model->save();

        $this->afterImportModel($model);
/*
        $this->output->writeln($arValues['name']);
        $this->output->writeln($model->name);
        $this->output->writeln('==');

     //   $this->output->writeln(implode(';', array_values($arValues)));

        if ($this->getProcessedCount() == 29) {
            $this->bCancel = true;
        }
*/
    }

    protected function afterImportModel($obModel): void
    {
        // nothinhg
    }


}
