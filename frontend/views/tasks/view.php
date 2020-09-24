<?php
/* @var $this yii\web\View */
/** @var TasksFilterForm $modelTasksFilter */
/** @var CategoriesFilterForm $modelCategoriesFilter */
/** @var array $modelTask */
/** @var array $modelsResponse */
/** @var array $modelTaskUser */


use frontend\models\forms\CategoriesFilterForm;
use frontend\models\forms\TasksFilterForm;
use TaskForce\Helpers\DeclinationNums;
use yii\helpers\Url;

$this->title = 'TaskForce - Задачи';

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
                                    <a href="#" class="link-regular"><?= $modelTask['cat_name'] ?></a>
                                    <?= $modelTask['afterTime'] ?> назад</span>
                        </div>
                        <b class="new-task__price new-task__price--<?= $modelTask['icon'] ?> content-view-price"><?= $modelTask['budget'] ?>
                            <b> ₽</b></b>
                        <div class="new-task__icon new-task__icon--<?= $modelTask['icon'] ?> content-view-icon"></div>
                    </div>
                    <div class="content-view__description">
                        <h3 class="content-view__h3">Общее описание</h3>
                        <p><?= $modelTask['description'] ?></p>
                    </div>
                    <div class="content-view__attach">
                        <h3 class="content-view__h3">Вложения</h3>
                        <?php /** @var array $modelsFiles */
                        if (count($modelsFiles) == 0) {
                            echo 'отсутствуют';
                        }
                        foreach ($modelsFiles as $key => $file):?>
                            <a href="<?= $file['task_id'] . '_' . $file['filename'] ?>"><?= $file['filename'] ?></a>
                        <?php endforeach; ?>
                    </div>
                    <div class="content-view__location">
                        <h3 class="content-view__h3">Расположение</h3>
                        <div class="content-view__location-wrapper">
                            <div class="content-view__map">
                                <a href="#"><img src="../../img/map.jpg" width="361" height="292"
                                                 alt="Москва, Новый арбат, 23 к. 1"></a>
                            </div>
                            <div class="content-view__address">
                                <span class="address__town">Москва</span><br>
                                <span><?= $modelTask['address'] ?></span>
                                <p>Вход под арку, код домофона 1122</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-view__action-buttons">
                    <button class=" button button__big-color response-button open-modal"
                            type="button" data-for="response-form">Откликнуться
                    </button>
                    <button class="button button__big-color refusal-button open-modal"
                            type="button" data-for="refuse-form">Отказаться
                    </button>
                    <button class="button button__big-color request-button open-modal"
                            type="button" data-for="complete-form">Завершить
                    </button>
                </div>
            </div>
            <div class="content-view__feedback">
                <?php
                if (!empty($modelsResponse)): ?>
                    <h2>Отклики <span>(<?= count($modelsResponse) ?>)</span></h2>
                <?php else: ?>
                    <h2>Нет откликов</h2>
                <?php endif; ?>

                <div class="content-view__feedback-wrapper">

                    <?php foreach ($modelsResponse as $key => $response): ?>
                        <div class="content-view__feedback-card">
                            <div class="feedback-card__top">
                                <a href="<?= Url::to(['users/view', 'id' => $response['profile_id']]) ?>">
                                    <img src="../../img/<?= $response['avatar'] ?>" width="55" height="55"></a>
                                <div class="feedback-card__top--name">
                                    <p><a href="#" class="link-regular"><?= $response['name'] ?></a></p>
                                    <?= str_repeat('<span></span>', $response['rate']); ?>
                                    <?= str_repeat('<span class="star-disabled"></span>', 5 - $response['rate']); ?>
                                    <b><?= $response['rate'] ?></b>
                                </div>
                                <span class="new-task__time"><?= DeclinationNums::getTimeAfter((string)$response['created_at']) ?> назад</span>
                            </div>
                            <div class="feedback-card__content">
                                <p>
                                    <?= $response['description'] ?>
                                </p>
                                <span><?= $response['price'] ?> ₽</span>
                            </div>
                            <div class="feedback-card__actions">
                                <a class="button__small-color request-button button"
                                   type="button">Подтвердить</a>
                                <a class="button__small-color refusal-button button"
                                   type="button">Отказать</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($modelTaskUser) ): ?>

            <section class="connect-desk">
                <div class="connect-desk__profile-mini">
                    <div class="profile-mini__wrapper">
                        <h3><?= ($current_user = 'customer') ? 'Исполнтель' : 'Заказчик' ?></h3>
                        <div class="profile-mini__top">
                            <img src="../../img/<?= $modelTaskUser['avatar'] ?>" width="62" height="62"
                                 alt="Аватар <?= ($current_user = 'customer') ? 'исполнтеля' : 'заказчика' ?>">
                            <div class="profile-mini__name five-stars__rate">
                                <p><?= $modelTaskUser['name'] ?></p>
                                <?= str_repeat('<span></span>', $modelTaskUser['rate']); ?>
                                <?= str_repeat('<span class="star-disabled"></span>', 5 - $modelTaskUser['rate']); ?>
                                <b><?= $modelTaskUser['rate'] ?></b>
                            </div>
                        </div>
                        <p class="info-customer"><span><?= $modelTaskUser['countTask'] ?> заданий</span>
                            <span class="last-"><?= DeclinationNums::getTimeAfter($modelTaskUser['date_add']) ?> на сайте</span>
                        </p>
                        <a href="<?= Url::to(['users/view', 'id' => $modelTaskUser['user_id']]) ?>"
                           class="link-regular">Смотреть профиль</a>
                    </div>
                </div>
                <div class="connect-desk__chat">
                    <h3>Переписка</h3>
                    <div class="chat__overflow">
                        <div class="chat__message chat__message--out">
                            <p class="chat__message-time">10.05.2019, 14:56</p>
                            <p class="chat__message-text">Привет. Во сколько сможешь
                                приступить к работе?</p>
                        </div>
                        <div class="chat__message chat__message--in">
                            <p class="chat__message-time">10.05.2019, 14:57</p>
                            <p class="chat__message-text">На задание
                                выделены всего сутки, так что через час</p>
                        </div>
                        <div class="chat__message chat__message--out">
                            <p class="chat__message-time">10.05.2019, 14:57</p>
                            <p class="chat__message-text">Хорошо. Думаю, мы справимся</p>
                        </div>
                    </div>
                    <p class="chat__your-message">Ваше сообщение</p>
                    <form class="chat__form">
                        <textarea class="input textarea textarea-chat" rows="2" name="message-text"
                                  placeholder="Текст сообщения"></textarea>
                        <button class="button chat__button" type="submit">Отправить</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<section class="modal response-form form-modal" id="response-form">
    <h2>Отклик на задание</h2>
    <form action="#" method="post">
        <p>
            <label class="form-modal-description" for="response-payment">Ваша цена</label>
            <input class="response-form-payment input input-middle input-money" type="text" name="response-payment"
                   id="response-payment">
        </p>
        <p>
            <label class="form-modal-description" for="response-comment">Комментарий</label>
            <textarea class="input textarea" rows="4" id="response-comment" name="response-comment"
                      placeholder="Place your text"></textarea>
        </p>
        <button class="button modal-button" type="submit">Отправить</button>
    </form>
    <button class="form-modal-close" type="button">Закрыть</button>
</section>
<section class="modal completion-form form-modal" id="complete-form">
    <h2>Завершение задания</h2>
    <p class="form-modal-description">Задание выполнено?</p>
    <form action="#" method="post">
        <input class="visually-hidden completion-input completion-input--yes" type="radio" id="completion-radio--yes"
               name="completion" value="yes">
        <label class="completion-label completion-label--yes" for="completion-radio--yes">Да</label>
        <input class="visually-hidden completion-input completion-input--difficult" type="radio"
               id="completion-radio--yet" name="completion" value="difficulties">
        <label class="completion-label completion-label--difficult" for="completion-radio--yet">Возникли
            проблемы</label>
        <p>
            <label class="form-modal-description" for="completion-comment">Комментарий</label>
            <textarea class="input textarea" rows="4" id="completion-comment" name="completion-comment"
                      placeholder="Place your text"></textarea>
        </p>
        <p class="form-modal-description">
            Оценка
        <div class="feedback-card__top--name completion-form-star">
            <span class="star-disabled"></span>
            <span class="star-disabled"></span>
            <span class="star-disabled"></span>
            <span class="star-disabled"></span>
            <span class="star-disabled"></span>
        </div>
        </p>
        <input type="hidden" name="rating" id="rating">
        <button class="button modal-button" type="submit">Отправить</button>
    </form>
    <button class="form-modal-close" type="button">Закрыть</button>
</section>
<section class="modal form-modal refusal-form" id="refuse-form">
    <h2>Отказ от задания</h2>
    <p>
        Вы собираетесь отказаться от выполнения задания.
        Это действие приведёт к снижению вашего рейтинга.
        Вы уверены?
    </p>
    <button class="button__form-modal button" id="close-modal"
            type="button">Отмена
    </button>
    <button class="button__form-modal refusal-button button"
            type="button">Отказаться
    </button>
    <button class="form-modal-close" type="button">Закрыть</button>
</section>

<div class="overlay"></div>
