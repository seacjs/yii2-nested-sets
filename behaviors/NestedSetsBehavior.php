<?php
/**
 * Created by PhpStorm.
 * User: seacjs
 * Date: 16.04.2018
 * Time: 12:07
 */

namespace seacjs\nestedsets\behaviors;

use yii\base\Behavior;
use yii\helpers\VarDumper;

class NestedSetsBehavior extends Behavior
{
    /**
     * @var string
     */
    public $leftAttribute = 'left_key';
    /**
     * @var string
     */
    public $rightAttribute = 'right_key';
    /**
     * @var string
     */
    public $levelAttribute = 'level';

    public function createNode()
    {
        $lastNode = $this->owner->find()->orderBy([$this->rightAttribute => SORT_DESC])->one();

        $this->updateCurrentNode(
            $lastNode == null ? 1 : $lastNode->{$this->rightAttribute} + 1,
            $lastNode == null ? 2 : $lastNode->{$this->rightAttribute} + 2,
            0
        );
    }

    /*
     * Update current node
     * */
    private function updateCurrentNode($leftKey, $rightKey, $level)
    {
        $this->owner->setAttribute($this->leftAttribute, $leftKey);
        $this->owner->setAttribute($this->rightAttribute, $rightKey);
        $this->owner->setAttribute($this->levelAttribute, $level);
        $this->owner->save();
    }

    /*
     * Update nodes as condition
     * */
    private function updateNodes($condition, $leftKeyShift, $rightKeyShift, $level)
    {
        foreach($this->owner->find()->andWhere($condition)->all() as $item) {
            $item->{$this->leftAttribute} += $leftKeyShift;
            $item->{$this->rightAttribute} += $rightKeyShift;
            $item->{$this->levelAttribute} += $level;
            $item->save();
        }
    }

    public function appendToNode($parent)
    {
        $this->updateNodes([
            '>', $this->leftAttribute, $parent->{$this->rightAttribute}
        ], 2, 2, 0);

        $this->updateNodes([
            'and',
            ['>=', $this->rightAttribute, $parent->{$this->rightAttribute}],
            ['<', $this->leftAttribute, $parent->{$this->rightAttribute}]
        ], 0, 2, 0);

        $this->updateCurrentNode(
            $parent->{$this->rightAttribute},
            $parent->{$this->rightAttribute} + 1,
            $parent->{$this->levelAttribute} + 1
        );
    }

    public function prependToNode($parent)
    {

        $this->updateNodes([
            '>', $this->leftAttribute, $parent->{$this->leftAttribute}
        ], 2, 2, 0);

        $this->updateNodes([
            'and',
            ['>=', $this->rightAttribute, $parent->{$this->rightAttribute}],
            ['<', $this->leftAttribute, $parent->{$this->rightAttribute}]
        ], 0, 2, 0);

        $this->updateCurrentNode(
            $parent->{$this->leftAttribute} + 1,
            $parent->{$this->leftAttribute} + 2,
            $parent->{$this->levelAttribute} + 1
        );

    }

    /* todo: insertBefore and insertAfter */
    public function insertBefore(){

    }
    public function insertAfter(){

    }

    public function removeNode()
    {
        $shift = -($this->owner->{$this->rightAttribute} - $this->owner->{$this->leftAttribute} + 1);

        $this->updateNodes([
            'and',
            ['>', $this->rightAttribute, $this->owner->{$this->rightAttribute}],
            ['<', $this->leftAttribute, $this->owner->{$this->leftAttribute}]
        ], 0, $shift, 0);

        $this->updateNodes([
            '>', $this->leftAttribute, $this->owner->{$this->rightAttribute}
        ], $shift, $shift,0);

        $this->owner->deleteAll([
            'and',
            ['>=', $this->leftAttribute, $this->owner->{$this->leftAttribute}],
            ['<=', $this->rightAttribute, $this->owner->{$this->rightAttribute}]
        ]);

    }

    public function moveNodeTo($newParentNode = null)
    {
        $left_key = $this->owner->{$this->leftAttribute};
        $right_key = $this->owner->{$this->rightAttribute};
        $level = $this->owner->{$this->levelAttribute};

        if($newParentNode == null) {
//            При переносе узла в корень дерева – максимальный правый ключ ветки;
//            SELECT MAX(right_key) FROM my_tree
            $right_key_near = $this->owner->find()->orderBy([$this->rightAttribute => SORT_DESC])->one()->{$this->rightAttribute};
        }
//        else if(false){
//            При поднятии узла на уровень выше – правый ключ старого родительского узла
//            SELECT right_key FROM my_tree WHERE level = $level
//            $right_key_near = 0;
//        }
        else if($this->getParent()->id == $newParentNode->id) {
//          При изменении порядка, когда родительский узел не меняется – правый ключ узла за которым будет стоять перемещаемый;
//          SELECT left_key, right_key FROM my_tree WHERE id = [id соседнего узла с который будет(!) выше (левее)]****
            $right_key_near = 0;
        } else {
//            При простом перемещении в другой узел;
//            SELECT (right_key – 1) AS right_key FROM my_tree WHERE id = [id нового родительского узла]
            $right_key_near = $newParentNode->{$this->rightAttribute} - 1;

        }

        $skew_tree = $right_key - $left_key + 1;
        $skew_level = ($newParentNode == null ? -1 : $newParentNode->{$this->levelAttribute}) - $level + 1;

        $id_edit = $this->owner->find()->andWhere([
            'and',
            ['>=', $this->leftAttribute, $left_key],
            ['<=', $this->rightAttribute, $right_key]
        ])->all();

        if($right_key > $right_key_near) {

            $skew_edit = $right_key_near - $left_key + 1;

            $this->updateNodes([
                'and',
                ['<', $this->rightAttribute, $left_key],
                ['>', $this->rightAttribute, $right_key_near]
            ], 0, $skew_tree, 0);
            $this->updateNodes([
                'and',
                ['<', $this->leftAttribute, $left_key],
                ['>', $this->leftAttribute, $right_key_near]
            ], $skew_tree, 0, 0);
        } else {

            $skew_edit = $right_key_near - $left_key + 1 - $skew_tree;

            $this->updateNodes([
                'and',
                ['>', $this->rightAttribute, $right_key],
                ['<=', $this->rightAttribute, $right_key_near],
            ], 0, -$skew_tree, 0);

            $this->updateNodes([
                'and',
                ['<', $this->leftAttribute, $left_key],
                ['>', $this->leftAttribute, $right_key_near],
            ], -$skew_tree, 0, 0);
        }

        $this->updateNodes([
            'in', 'id', $id_edit
        ], $skew_edit, $skew_edit, $skew_level);

        /*При перемещении вверх по дереву выделяем следующие области:*/

        //1. UPDATE my_tree SET right_key = right_key + $skew_tree WHERE right_key < $left_key AND right_key > $right_key_near
        //2. UPDATE my_tree SET left_key = left_key + $skew_tree WHERE left_key < $left_key AND left_key > $right_key_near
        //Теперь можно переместить ветку:
        //UPDATE my_tree SET left_key = left_key + $skew_edit, right_key = right_key + $skew_edit, level = level + $skew_level WHERE id IN ($id_edit)

        /*При перемещении вниз по дереву выделяем следующие области:*/
        //1. UPDATE my_tree SET right_key = right_key - $skew_tree WHERE right_key > $right_key AND right_key <= $right_key_near
        //2. UPDATE my_tree SET left_key = left_key - $skew_tree WHERE left_key < $left_key AND left_key > $right_key_near
        //Теперь можно переместить ветку:
        //UPDATE my_tree SET left_key = left_key + $skew_edit, right_key = right_key + $skew_edit, level = level + $skew_level WHERE id IN ($id_edit)

    }

    public function tree() {
        return $this->owner->find()->orderBy($this->leftAttribute)->all();
    }

    public function getParent()
    {
        return $this->owner->find()->andWhere([
            'and',
            ['<', $this->leftAttribute, $this->owner->{$this->leftAttribute}],
            ['>', $this->rightAttribute, $this->owner->{$this->rightAttribute}]
        ])->andWhere([
            $this->levelAttribute => $this->owner->{$this->levelAttribute} - 1
        ])->one();
    }

    public function getChildren()
    {


    }

    const OPERATION_MAKE_ROOT = 'makeRoot';
    const OPERATION_PREPEND_TO = 'prependTo';
    const OPERATION_APPEND_TO = 'appendTo';
    const OPERATION_INSERT_BEFORE = 'insertBefore';
    const OPERATION_INSERT_AFTER = 'insertAfter';
    const OPERATION_DELETE_WITH_CHILDREN = 'deleteWithChildren';

    public function show($items)
    {
        $data = [];
        foreach($items as $item) {
            $data[] = [
                'title' => $item->name,
                'key' => $item->id
            ];
        }
    }

    /* STOP HERE */
    public $items = [];
    public function makeTree($items)
    {
        $this->items = $items;
        return $this->buildTree();
    }

    public function buildTree()
    {
        $children = [];

        while(count($this->items) > 0) {

            $currentItem = $this->items[0];
            $nextItem = isset($this->items[1]) ? $this->items[1] : null;

            $dataItem = [
                'title' => $currentItem->name,
                'key' => $currentItem->id
            ];

            array_shift($this->items);

            if($currentItem->{$this->leftAttribute} + 1 == $currentItem->{$this->rightAttribute}) {
                $children[] = $dataItem;
                if($nextItem != null &&($currentItem->{$this->rightAttribute} + 1 != $nextItem->{$this->leftAttribute})) {
                    return $children;
                }
            } else {

                if($nextItem != null && ($currentItem->{$this->leftAttribute} + 1 == $nextItem->{$this->leftAttribute})) {
                    $dataItem['children'] = $this->buildTree($this->items);
                    $children[] = $dataItem;

                } else {
                    return $children;
                }

            }

        }

        return $children;

    }



}