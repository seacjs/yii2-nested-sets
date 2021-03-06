<?php

namespace seacjs\nestedsets\components;

use yii\base\Action;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;

/**
 * Nested Sets Insert before Action
 */
class NestedSetsInsertBeforeAction extends NestedSetsBaseAction
{

    public function run()
    {
        $this->_model->insertAfter($this->_modelNear);

        return json_encode(true);
    }

}