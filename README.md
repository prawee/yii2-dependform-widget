# How to validate multiple model in single form

## Example us case

Have 2 table need to save as the same time and depend on you pick amount of member of team

```bash
team
- id
- name
- member

person
- id
- team_id
- name
- position
- experience
```

## Usage

### View

```bash
use prawee\widgets\DependWidget;
...
$form = ActiveForm::begin(['id' => 'depend-form']);
echo $form->field($team, 'name', ['options' => ['class' => 'col-md-8']])->textInput(['autofocus' => true]);
echo $form->field($team, 'member', ['options' =>['class' => 'col-md-4']])->dropDownList([
    1 => 1,
    2 => 2,
    3 => 3,
    5 => 5,
    8 => 8,
    10 => 10,
],['prompt' => 'select']);
echo DependWidget::widget([
    'form' => $form,
    'model' => $person,
    'attributes' => [
        'name',
        'position',
        'experience' => [
            'type' => 'select',
            'list' =>[1 => '1-2', 2 => '3-5', 3 => '5 >', 4 => '10 >']
        ],
    ],
    'attributeOptions' => ['class' => 'col-md-4'],
    'depends' => [Html::getInputId($team, 'member')]
]);
?>
<div class="form-group col-md-12">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>
<?php ActiveForm::end(); ?>
```

![alt normal](https://github.com/prawee/yii2-dependform-widgets/blob/master/src/images/yii2-validate-muliple-model-1.png)

![alt action](https://github.com/prawee/yii2-dependform-widgets/blob/master/src/images/yii2-validate-muliple-model-2.png)

### Controller

```bash
...
public function actionCreate()
{
    $team = new Team();
    $person = new Person();

    $post = \Yii::$app->request->post();
    if (Model::loadMultiple([$person], $post) && Model::validateMultiple([$person])) {
        $team->load($post['Team']);
        foreach($post['Person'] as $person):
            $person->load($person);
            $person->team_id = $team->id;
            $person->save();
        endforeach;
        $team->save();
        return $this->redirect(['index']);
    }

    return $this->render('create', [
        'team' => $team,
        'person' => $person
    ]);
}
...
```