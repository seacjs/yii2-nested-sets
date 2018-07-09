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
    protected $_model;
    protected $_modelNear;
    protected $_post;

    public function beforeRun()
    {
        if (parent::beforeRun()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $request = Yii::$app->request;
            $this->_post = Yii::$app->request->post();
            if($request->isAjax && $request->isPost){
                $this->_model = (new $this->modelClassName())->findOne(['id' => $this->_post['id']]);
                $this->_modelNear = (new $this->modelClassName())->findOne(['id' => $this->_post['id_near']]);
                if($this->_model != null && $this->_modelNear != null) {
                    return true;
                }
            }
        }

        return false;
    }

}