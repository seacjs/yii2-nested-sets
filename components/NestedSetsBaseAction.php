<?php

namespace seacjs\nestedsets\components;

use yii\base\Action;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;

/**
 * Nested Sets Insert before Action
 */
class NestedSetsBaseAction extends Action
{
    public $modelClassName;
    private $_model;
    private $_modelNear;
    private $_post;

    public function beforeRun()
    {
        if (parent::beforeRun()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->_post = Yii::$app->request->post();
            if(Yii::$app->request->isAjax && $this->_post){
                $this->_model = (new $this->modelClassName())->findOne($this->_post['id']);
                $this->_modelNear = (new $this->modelClassName())->findOne($this->_post['id_near']);
                if($this->_model != null && $this->_modelNear != null) {
                    return true;
                }
            }
        }

        return false;
    }

}