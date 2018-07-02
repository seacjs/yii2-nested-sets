<?php
/**
 * @link      https://github.com/wbraganca/yii2-fancytree-widget
 * @copyright Copyright (c) 2014 Wanderson Bragança
 * @license   https://github.com/wbraganca/yii2-fancytree-widget/blob/master/LICENSE
 */

namespace seacjs\nestedsets\widgets;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\JsExpression;

/**
 * The yii2-fancytree-widget is a Yii 2 wrapper for the fancytree.js
 * See more: https://github.com/mar10/fancytree
 *
 * @author Wanderson Bragança <wanderson.wbc@gmail.com>
 */
class FancytreeWidget extends \yii\base\Widget
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerAssets();
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        FancytreeAsset::register($view);
        if(in_array('table',$this->options['extensions'])) {
            $id = 'treetable';
        } else {
            $id = 'fancyree_' . $this->id;
        }

        if (isset($this->options['id'])) {
            $id = $this->options['id'];
            unset($this->options['id']);
        } else {
            if(in_array('table', $this->options['extensions'])) {
                echo '<table id="'.$id.'"  class="table table-hover">
                    <colgroup>
                        <col width="30px"></col>
                        <col width="30px"></col>
                        <col width="*"></col>
                        <col width="50px"></col>
                        <col width="100px"></col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>node</th>
                            <th>id</th>
                            <th>actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td>#</td>
                            <td>node</td>
                            <td>id</td>
                            <td>action buttons</td>
                        </tr>
                    </tbody>
                </table>';
                if(!array_key_exists('table', $this->options)) {
                    $this->options['table'] = [
                        'indentation' => 20,      // indent 20px per node level
                        'nodeColumnIdx' => 2,     // render the node title into the 2nd column
                        'checkboxColumnIdx' => 0  // render the checkboxes into the 1st column
                    ];
                }
                if(!array_key_exists('renderColumns', $this->options)) {
                    $this->options['renderColumns'] = new JsExpression('function(event, data) {
                        var node = data.node,
                        $tdList = $(node.tr).find(">td");
                        $tdList.eq(1).text(node.getIndexHier()).addClass("alignRight");
                        $tdList.eq(3).text(node.key);
                        $tdList.eq(4).html(\'' . $this->generateActionButtons() . '\');
                     }');
                }
            } else {
                echo Html::tag('div', '', ['id' => $id]);
            }
        }
        $options = Json::encode($this->options);
        $view->registerJs('$("#' . $id . '").fancytree( ' .$options .')');
    }
    public function generateActionButtons() {

        $url = '"node.key"';

        $add = Html::tag('span','',['class' => 'glyphicon glyphicon-plus']);
        $view = Html::tag('span','',['class' => 'glyphicon glyphicon-eye-open']);
        $update = Html::tag('span','',['class' => 'glyphicon glyphicon-pencil']);
        $delete = Html::tag('span','',['class' => 'glyphicon glyphicon-trash']);

        return Html::a($add, $url,[
                'title' => 'add',
                'role' => 'modal-remote',
                'data' => [
                    'toggle' => 'tooltip'
                ]
            ]) . '&nbsp;' .
            Html::a($view, $url,[
                'title' => 'view',
                'role' => 'modal-remote',
                'data' => [
                    'toggle' => 'tooltip'
                ]
            ]) . '&nbsp;' .
            Html::a($update, $url,[
                'title' => 'update',
                'role' => 'modal-remote',
                'data' => [
                    'toggle' => 'tooltip'
                ]
            ]) . '&nbsp;' .
            Html::a($delete, $url,[
                'title' => 'delete',
                'role' => 'modal-remote',
                'data' => [
                    'pjax' => '0',
                    'toggle' => 'tooltip',
                    'request-method' => 'post',
                    'confirm-title' => 'Are you sure?',
                    'confirm-message' => 'Are you sure want to delete this item'
                ]
            ]);

    }
}
