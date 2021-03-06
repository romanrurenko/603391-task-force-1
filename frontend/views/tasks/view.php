<?php

/* @var $this yii\web\View */
/** @var TasksFilterForm $modelTasksFilter */
/** @var CategoriesFilterForm $modelCategoriesFilter */
/** @var array $modelTask */
/** @var array $modelsResponse */
/** @var array $assistUserModel */
/** @var ResponseTaskForm $responseTaskForm */
/** @var CompleteTaskForm $completeTaskForm */
/** @var bool $existsUserResponse */
/** @var array $availableActions */

use frontend\assets\TaskViewAsset;
use frontend\models\forms\CategoriesFilterForm;
use frontend\models\forms\CompleteTaskForm;
use frontend\models\forms\ResponseTaskForm;
use frontend\models\forms\TasksFilterForm;
use TaskForce\widgets\RatingWidget;
use TaskForce\widgets\YandexMapWidget;
use TaskForce\Actions\CancelAction;
use TaskForce\Actions\CompleteAction;
use TaskForce\Actions\RefuseAction;
use TaskForce\Actions\ResponseAction;
use TaskForce\Constant\UserRole;
use TaskForce\Helpers\Declination;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

TaskViewAsset::register($this);
$currentUserId = Yii::$app->user->getId();
$messageUrl = Yii::$app->params['apiURL'] .  Url::toRoute(['v1/messages']);
$scriptJS = <<<TAG
window.messageApiUrl = '$messageUrl';
TAG;
$this->registerJs($scriptJS, yii\web\View::POS_BEGIN);

?>
<main class="page-main">
    <div class="main-container page-container">
        <section class="content-view">
            <div class="content-view__card">
                <div class="content-view__card-wrapper">
                    <div class="content-view__header">
                        <div class="content-view__headline">
                            <h1><?=
                                $modelTask['name'] ?></h1>
                            <span>Размещено в категории
                                    <a href="<?= Url::to(['tasks/index/', 'category' => $modelTask['category_id']]) ?>"
                                       class="link-regular"><?= $modelTask['cat_name'] ?></a>
                                    <?= $modelTask['afterTime'] ?></span>
                        </div>
                        <b class="new-task__price new-task__price--<?= $modelTask['icon'] ?> content-view-price"><?= $modelTask['budget'] ?>
                            <b> ₽</b></b>
                        <div class="new-task__icon new-task__icon--<?= $modelTask['icon'] ?> content-view-icon"></div>
                    </div>
                    <div class="content-view__description">
                        <h3 class="content-view__h3">Общее описание</h3>
                        <p><?= strip_tags($modelTask['description']) ?></p>
                    </div>
                    <?php
                    /** @var array $modelsFiles */
                    if (count($modelsFiles) > 0):?>
                        <div class="content-view__attach">
                            <h3 class="content-view__h3">Вложения</h3>
                            <?php
                            foreach ($modelsFiles as $key => $file):?>
                                <a href="<?= Url::to(['site/file', 'id' => $file['id']]) ?>"
                                   title="<?= $file['filename'] ?>">
                                    <?= (strlen($file['filename']) > 30)
                                        ? (substr($file['filename'], 0, 30) . '...')
                                        : $file['filename'] ?></a>
                            <?php
                            endforeach; ?>
                        </div>
                    <?php
                    endif;

                    if ((int)$modelTask['lat'] !== 0 && (int)$modelTask['lng'] !== 0):?>
                        <div class="content-view__location">
                            <h3 class="content-view__h3">Расположение</h3>
                            <div class="content-view__location-wrapper">
                                 <?= YandexMapWidget::widget(['lat'=>$modelTask['lat'],'lng'=>$modelTask['lng']]);?>
                                <div class="content-view__address">
                                    <span class="address__town"><?= $modelTask['city'] ?></span><br>
                                    <span><?= $modelTask['address'] ?></span>
                                    <p></p>
                                </div>
                            </div>
                        </div>
                    <?php
                    endif; ?>
                </div>
                <div class="content-view__action-buttons">

                    <?php
                    foreach ($availableActions as $key => $action) {
                        switch ($action) {
                            case ResponseAction::getTitle():
                                if ($existsUserResponse) {
                                    break;
                                }
                                echo '<button class="button button__big-color response-button open-modal"
                                type="button" data-for="response-form">Откликнуться</button>';
                                break;
                            case RefuseAction::getTitle():
                                echo '<button class="button button__big-color refusal-button open-modal"
                            type="button" data-for="refuse-form">Отказаться</button>';
                                break;
                            case CompleteAction::getTitle():
                                echo '<button class="button button__big-color request-button open-modal"
                            type="button" data-for="complete-form">Завершить</button>';
                                break;
                            case CancelAction::getTitle():
                                echo '<button class="button button__big-color refusal-button open-modal"
                            type="button" data-for="cancel-form">Отменить</button>';
                                break;
                            default:
                                break;
                        }
                    } ?>

                </div>
            </div>
            <div class="content-view__feedback">
                <?php
                if (!empty($modelsResponse)): ?>
                    <h2>Отклики <span>(<?= count($modelsResponse) ?>)</span></h2>
                <?php
                endif; ?>

                <div class="content-view__feedback-wrapper">

                    <?php
                    foreach ($modelsResponse as $key => $response): ?>
                        <div class="content-view__feedback-card">
                            <div class="feedback-card__top">
                                <a href="<?= Url::to(['users/view', 'id' => $response['user_id']]) ?>">
                                    <img src="<?= Url::base(
                                    ) . '/uploads/avatars/' . ($response['avatar'] ?? 'no-avatar.jpg') ?>"
                                         width="55" height="55" alt="avatar"></a>
                                <div class="feedback-card__top--name">
                                    <p><a href="<?= Url::to(['users/view', 'id' => $response['user_id']]) ?>"
                                          class="link-regular"><?= strip_tags($response['name']) ?></a></p>
                                    <?= RatingWidget::widget(['rate' => $response['rate'] ?? 0]) ?>
                                </div>
                                <span class="new-task__time"><?= Declination::getTimeAfter(
                                        (string)$response['created_at']
                                    ) ?></span>
                            </div>
                            <div class="feedback-card__content">
                                <p>
                                    <?= strip_tags($response['description']) ?>
                                </p>
                                <span><?= $response['price'] ?> ₽</span>
                            </div>
                            <?php
                            if (Yii::$app->user->identity->role === UserRole::CUSTOMER
                                && ((int)$modelTask['customer_id']) === $currentUserId
                                && ($response['status'] === TaskForce\ResponseEntity::STATUS_NEW)
                                && ($modelTask['status'] === TaskForce\TaskEntity::STATUS_NEW)
                            ):?>
                                <div class="feedback-card__actions">

                                    <?= Html::a(
                                        'Подтвердить',
                                        [
                                            'response/confirm',
                                            'id' => $response['id']
                                        ],
                                        ['class' => 'button__small-color request-button button']
                                    ) ?>
                                    <?= Html::a(
                                        'Отказать',
                                        [
                                            'response/cancel',
                                            'id' => $response['id']
                                        ],
                                        ['class' => 'button__small-color refusal-button button']
                                    ) ?>

                                </div>
                            <?php
                            endif; ?>
                        </div>
                    <?php
                    endforeach; ?>
                </div>
            </div>
        </section>

        <?php
        if (!empty($assistUserModel)): ?>

            <section class="connect-desk">
                <div class="connect-desk__profile-mini">
                    <div class="profile-mini__wrapper">

                        <h3><?php
                            $showExecutor = ((int)$modelTask['customer_id'] === $currentUserId
                                && $modelTask['executor_id'] !== null);

                            echo ($showExecutor) ? 'Исполнтель' : 'Заказчик' ?></h3>
                        <div class="profile-mini__top">
                            <img src="<?= Url::base() . '/uploads/avatars/' . $assistUserModel['avatar'] ?>" width="62"
                                 height="62"
                                 alt="Аватар <?= ($showExecutor) ? 'исполнтеля' : 'заказчика' ?>">
                            <div class="profile-mini__name five-stars__rate">
                                <p><?= $assistUserModel['name'] ?></p>
                                <?= RatingWidget::widget(['rate' => $assistUserModel['rate']]) ?>
                            </div>
                        </div>
                        <p class="info-customer"><span><?= $assistUserModel['countTask'] ?> заданий</span>
                            <span class="last-"><?= Declination::getTimeAfter(
                                    $assistUserModel['date_add']
                                ) ?> на сайте</span>
                        </p>
                        <?php
                        if ($assistUserModel['isExecutor']): ?>
                            <a href="<?= Url::to(['users/view', 'id' => $assistUserModel['user_id']]) ?>"
                               class="link-regular">Смотреть профиль</a>
                        <?php
                        endif; ?>
                    </div>
                </div>
                <?php
                if ((((int)$modelTask['customer_id']) === $currentUserId
                        || ((int)$modelTask['executor_id']) === $currentUserId)
                    && ($modelTask['executor_id'] !== null)
                ): ?>
                <div id="chat-container">
                    <chat class="connect-desk__chat" task="<?=$modelTask['id']?>"></chat>
                <?php
                endif; ?>
            </section>
        <?php
        endif; ?>
    </div>
</main>
<section class="modal response-form form-modal" id="response-form">
    <h2>Отклик на задание</h2>
    <?php

    $form = ActiveForm::begin(
        [
            'action' => ['task/response', 'id' => $modelTask['id']],
            'enableClientValidation' => true,
            'fieldConfig' => [
                'template' => "<p>{label}{input}{error}</p>",
                'labelOptions' => ['class' => 'form-modal-description'],
                'errorOptions' => ['tag' => 'span', 'style' => 'color:red'],
            ],
        ]
    );

    echo $form
        ->field($responseTaskForm, 'payment')
        ->label('Ваша цена')
        ->input(
            'text',
            [
                'class' => 'response-form-payment input input-middle input-money',
            ]
        );

    echo $form
        ->field($responseTaskForm, 'comment')
        ->label('Комментарий')
        ->textarea(
            [
                'rows' => 4,
                'placeholder' => 'Place your text',
                'class' => 'input textarea'
            ]
        );


    echo Html::submitButton(
        'Отправить',
        [
            'class' => 'button modal-button'
        ]
    );

    ActiveForm::end(); ?>
    <button class="form-modal-close" type="button">Закрыть</button>

</section>
<section class="modal completion-form form-modal" id="complete-form">
    <h2>Завершение задания</h2>
    <p class="form-modal-description">Задание выполнено?</p>
    <?php
    $form = ActiveForm::begin(
        [
            'action' => ['task/complete', 'id' => $modelTask['id']],
            'fieldConfig' => [
                'template' => "<p>{label}</br>{input}</p>{error}"
            ]
        ]
    ); ?>

    <?=
    Html::activeRadioList(
        $completeTaskForm,
        'completion',
        [
            'yes' => 'Да',
            'difficult' => 'Возникли проблемы'
        ],
        [
            'item' => function ($label, $name, $checked, $value) {
                $radio = Html::radio(
                    $name,
                    $checked,
                    [
                        'id' => $value,
                        'value' => $value,
                        'class' => 'visually-hidden completion-input completion-input--' . $value
                    ]
                );
                $label = Html::label(
                    $label,
                    $value,
                    [
                        'class' => 'completion-label completion-label--' . $value
                    ]
                );
                return $radio . $label;
            }
        ]
    ); ?>

    <?= $form->field($completeTaskForm, 'comment', ['template' => '<p>{label}{input}{error}</p>'])
        ->label('Комментарий', ['class' => 'form-modal-description'])
        ->textarea(
            [
                'class' => 'input textarea',
                'rows' => 4,
                'placeholder' => 'Place your text',
                'id' => "completion-comment",
            ]
        ); ?>


    <?= $form->field(
        $completeTaskForm,
        'rating',
        [
            'template' =>
                "<p class=\"form-modal-description\">Оценка
              <div class='feedback-card__top--name completion-form-star'>
                <span class='star-disabled'></span>
                <span class='star-disabled'></span>
                <span class='star-disabled'></span>
                <span class='star-disabled'></span>
                <span class='star-disabled'></span>
              </div>
          </p>{input}{error}"
        ]
    )->hiddenInput(['id' => 'rating']); ?>
    <?= Html::submitButton(
        'Отправить',
        [
            'class' => 'button modal-button'
        ]
    ) ?>
    <?php
    ActiveForm::end(); ?>


    <button class="form-modal-close" type="button">Закрыть</button>
</section>
<section class="modal form-modal refusal-form" id="refuse-form">
    <h2>Отменить задание</h2>
    <p>
        Вы собираетесь отказаться от выполнения задания.
        Это действие приведёт к снижению вашего рейтинга.
        Вы уверены?
    </p>
    <button class="button__form-modal button" id="close-modal"
            type="button">Отмена
    </button>

    <?php
    $form = ActiveForm::begin(
        [
            'action' => ['task/refuse', 'id' => $modelTask['id']],
        ]
    );

    echo Html::submitButton(
        'Отменить задание',
        [
            'class' => 'button__form-modal refusal-button button'
        ]
    );
    ActiveForm::end(); ?>


    <button class="form-modal-close" type="button">Закрыть</button>
</section>
<section class="modal form-modal refusal-form" id="cancel-form">
    <h2>Отменить задание</h2>
    <p>
        Вы собираетесь отменить задание.
        Вы уверены?
    </p>
    <button class="button__form-modal button" id="close-modal"
            type="button">Отмена
    </button>

    <?php
    $form = ActiveForm::begin(
        [
            'action' => ['task/cancel', 'id' => $modelTask['id']],
        ]
    );

    echo Html::submitButton(
        'Отменить задание',
        [
            'class' => 'button__form-modal refusal-button button'
        ]
    );
    ActiveForm::end(); ?>


    <button class="form-modal-close" type="button">Закрыть</button>
</section>


