<?php namespace Kdz1\Import;

use Yaml;

/**
 * Class ImportModel
 */
class ImportModel extends ImportProcessor
{
    protected $modelClass;
    protected $key;

    public function __construct(array $arConfig)
    {
        parent::__construct($arConfig);
        $this->modelClass = array_get($this->getConfig(), 'modelClass');
        $this->key = array_get($this->getConfig(), 'key', 'id');
    }

    public function importRec($arValues): void
    {
        $arKeys = explode(';', $this->key);

        $q = $this->modelClass::query();
        foreach ($arKeys as $k) {
            $q->where($k, $arValues[$k]);
        }

        $model = $q->first();

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
    }

    protected function afterImportModel($obModel): void
    {
        // nothinhg
    }


}
