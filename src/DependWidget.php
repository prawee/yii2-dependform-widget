<?php
/**
 * @link http://www.prawee.com
 * 23/06/2020 4:22 PM
 * @copyright Copyright (c) 2020 served
 * @author Prawee Wongsa <prawee@hotmail.com>
 * @license BSD-3-Clause
 */
namespace prawee\widgets;

use Yii;
use yii\widgets\Pjax;
use yii\bootstrap4\Html;
use yii\bootstrap4\Widget;

class DependWidget extends Widget
{
    public $form;
    public $model;
    public $attributes = [];
    public $options = ['class' => 'col-md-12'];
    public $rowOptions = ['class' => 'row'];
    public $attributeOptions = ['class' => 'col-md-4'];
    public $depends = [];
    public $amount = 0;
    public $amountId;

    public function __construct($config = [])
    {
        $this->amountId = $this->id.'-amount';
        parent::__construct($config);
    }

    public function run()
    {
        $this->setAsset();
        $this->getAmount();

        Pjax::begin(['id' => $this->id.'-pjax', 'enablePushState' => false, 'timeout' => false]);
        echo Html::beginTag('div', array_merge($this->options, ['id' => $this->id.'-content']));
        for($i=0; $i < $this->amount; $i++) {
            $this->renderField($i);
        }
        echo Html::endTag('div');
        Pjax::end();
    }

    public function getAmount()
    {
        if (Yii::$app->request->enableCookieValidation) {
            $this->amount = (int)$_COOKIE[$this->amountId];
        } else {
            $this->amount = (int)Yii::$app->getRequest()->getCookies()->get($this->amountId);
        }
    }

    public function renderField($index)
    {
        echo Html::beginTag('div', $this->rowOptions);
        foreach($this->attributes as $key => $val) :
            if (is_array($val)) {
                $field = $this->form->field($this->model, "[$index]$key", ['options' => $this->attributeOptions]);
                switch ($val['type']) {
                    case 'select':
                        $field->dropDownList($val['list']);
                        break;
                    default:
                        $field->textInput();
                        break;
                }
            } else {
                $field = $this->form->field($this->model, "[$index]$val", ['options' => $this->attributeOptions]);
                $field->textInput();
            }
            echo $field;
        endforeach;
        echo Html::endTag('div');
    }

    public function setAsset()
    {
        $dependId = $this->depends[0];
        $js = <<<EOF
        document.cookie = "$this->amountId=0";
        $("#$this->id-content").addClass('hide');
        
        $("#$dependId").on("change", function() {
            var curAmount = $(this).val();
            console.log('curAmount', curAmount);
            if (curAmount > 0) {
                document.cookie = "$this->amountId="+curAmount;
                $("#$this->id-content").removeClass('hide');
                $.pjax.reload({ container: "#$this->id-pjax", async:false });
            } else {
                document.cookie = "$this->amountId=0";
                $("#$this->id-content").addClass('hide');
            }  
        });
EOF;
        $this->view->registerJs($js);
    }
}