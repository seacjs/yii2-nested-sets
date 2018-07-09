<?php

namespace seacjs\nestedsets\components;

use yii\base\Action;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;

/**
 * Nested Sets Insert before Action
 */
class NestedSetsChangeAction extends NestedSetsBaseAction
{

    public function run()
    {
        // before, after, over
        $hitMode = $this->_post['hit_mode'];

//        VarDumper::dump($this->_modelNear->name,10,1);die;
//        VarDumper::dump((new $this->modelClassName())->findOne(1)->name,10,1);die;

//        return json_encode($this->modelClassName);

        if($hitMode === 'over') {
            $this->_model->moveNodeTo($this->_modelNear);
            return json_encode('over');
        } elseif($hitMode === 'before') {
            $this->_model->insertBefore($this->_modelNear);
            return json_encode('before');
        } elseif($hitMode === 'after') {
            $this->_model->insertAfter($this->_modelNear);
            return json_encode('after');

        } else {
            return json_encode(false);
        }

        return json_encode(true);
    }

}