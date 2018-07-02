<?php

namespace seacjs\nestedsets\components;

use yii\base\Action;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;

/**
 * Nested Sets Insert before Action
 */
class NestedSetsRemoveAction extends NestedSetsBaseAction
{

    public function run()
    {
        $this->_model->removeNode();

        return json_encode(true);
    }

}