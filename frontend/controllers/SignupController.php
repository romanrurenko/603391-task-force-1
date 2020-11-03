<?php

namespace frontend\controllers;

use frontend\models\City;
use frontend\models\forms\LoginForm;
use frontend\models\forms\SignupForm;
use TaskForce\Exception\TaskForceException;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class SignupController extends Controller
{

    /**
     * @return array|array[]
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?']
                    ],
                ]
            ],
        ];
    }

    /**
     * @return string|Response
     * @throws TaskForceException
     */
    public function actionIndex()
    {
        $model = new SignupForm();

        if (Yii::$app->request->getIsPost()) {
            $model->load(Yii::$app->request->post());

            if ($model->validate() && $model->register()) {
                Yii::$app->session->setFlash('success', 'Пользователь зарегистрирован.');
                return $this->goHome();
            }
        }

        $cities = City::getList();

        return $this->render('index', compact('model', 'cities'));
    }

}
