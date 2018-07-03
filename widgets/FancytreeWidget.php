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
use yii\web\View;

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
    public $url;

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
                echo '<style>table.fancytree-ext-table tbody tr.fancytree-active {background-color: #eee;}</style>';
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
                /*todo: lol replace regexp 4 repetitions =) */
                if(!array_key_exists('renderColumns', $this->options)) {
                    $this->options['renderColumns'] = new JsExpression('function(event, data) {
                        var node = data.node;
                        var buttons = \'' . $this->generateActionButtons() . '\';

                        buttons = buttons.replace("node.key", node.key);
                        buttons = buttons.replace("node.key", node.key);
                        buttons = buttons.replace("node.key", node.key);
                        buttons = buttons.replace("node.key", node.key);

                        $tdList = $(node.tr).find(">td");
                        $tdList.eq(1).text(node.getIndexHier()).addClass("alignRight");
                        $tdList.eq(3).html(node.key);
                        $tdList.eq(4).html(buttons);
                     }');
                }
            } else {
                echo Html::tag('div', '', ['id' => $id]);
            }
        }
        $options = Json::encode($this->options);
        $view->registerJs('$("#' . $id . '").fancytree( ' .$options .');');

        $view->registerJs('$("#' . $id . ' tr").each(function(key, value){ console.log(value.find("td"))});');

    }

    public function generateActionButtons() {

        $url = "node.key";

        $add = Html::tag('span','',['class' => 'glyphicon glyphicon-plus']);
        $view = Html::tag('span','',['class' => 'glyphicon glyphicon-eye-open']);
        $update = Html::tag('span','',['class' => 'glyphicon glyphicon-pencil']);
        $delete = Html::tag('span','',['class' => 'glyphicon glyphicon-trash']);

        return Html::a($add, $this->url  . 'create/' . $url,[
                'title' => 'add',
                'role' => 'modal-remote',
                'data' => [
                    'toggle' => 'tooltip'
                ]
            ]) . '&nbsp;' .
            Html::a($view, $this->url  . 'view/' . $url,[
                'title' => 'view',
                'role' => 'modal-remote',
                'data' => [
                    'toggle' => 'tooltip'
                ]
            ]) . '&nbsp;' .
            Html::a($update, $this->url  . 'update/' . $url,[
                'title' => 'update',
                'role' => 'modal-remote',
                'data' => [
                    'toggle' => 'tooltip'
                ]
            ]) . '&nbsp;' .
            Html::a($delete, $this->url  . 'delete/' . $url,[
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

