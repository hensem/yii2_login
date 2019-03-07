<?php

namespace app\controllers;

use Yii;
use app\models\Users;
use app\models\UsersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\ResendForm;
use app\models\RecoveryForm;
use yii\helpers\Html;
use app\models\Token;
use app\models\Auth;

use app\components\AuthHandler;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Users model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Users();
  
        if (!empty(Yii::$app->request->post())) {
  
            if ($model->register(Yii::$app->request->post())) {
                return $this->render('welcome', ['email' => $model->email]);
                Yii::$app->end();
           } else {
               return $this->render('create', [
                   'model' => $model,
               ]);
               Yii::$app->end();
          }

        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
	
	/**
     * Confirms user's account. If confirmation was successful logs the user and shows success message. Otherwise
     * shows error message.
     *
     * @param int    $id
     * @param string $code
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionConfirm($id, $code)
    {
        $user = Users::findOne($id);

        $user->attemptConfirmation($id, $code);

        return $this->redirect(['site/index']);
    }

    /**
     * Displays page where user can request new confirmation token. If resending was successful, displays message.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionResend()
    {

        /** @var ResendForm $model */
        $model = new ResendForm;
  
        if (!empty(Yii::$app->request->post())) {
            if ($model->load(\Yii::$app->request->post()) && $model->resend()) {
                return $this->redirect(['site/index']);
                Yii::$app->end();
            }
        }

        return $this->render('resend', [
            'model' => $model,
        ]);
    }
	
	/**
     * Shows page where user can request password recovery.
     *
     * @return string
     */
    public function actionRequest()
    {

        /** @var RecoveryForm $model */
        $model = new RecoveryForm;
        $model->scenario = RecoveryForm::SCENARIO_REQUEST;

        if (!empty(Yii::$app->request->post())) {
            if ($model->load(\Yii::$app->request->post()) && $model->sendRecoveryMessage()) {
                return $this->redirect(['site/index']);
                Yii::$app->end();
            }
        }

        return $this->render('request', [
            'model' => $model,
        ]);
    }

    /**
     * Displays page where user can reset password.
     *
     * @param int    $id
     * @param string $code
     *
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionReset($id, $code)
    {

        /** @var Token $token */
        $token = Token::find()->where(['uid' => $id, 'code' => $code, 'type' => Token::TYPE_RECOVERY])->one();
        if (!$token || $token->isExpired()) {
            \Yii::$app->session->setFlash(
                'danger',
               'Recovery link is invalid or expired. Please try '.Html::a('requesting', ['/users/request']).' a new one.'
            );
            return $this->redirect(['site/login']);
            Yii::$app->end();
        }

        /** @var RecoveryForm $model */
        $model = new RecoveryForm;
        $model->scenario = RecoveryForm::SCENARIO_RESET;

        if (!empty(Yii::$app->request->post())) {
            if ($model->load(\Yii::$app->getRequest()->post()) && $model->resetPassword($token)) {
                \Yii::$app->session->setFlash(
                    'success',
                    'Password has been changed'
                );
                return $this->redirect(['site/login']);
                Yii::$app->end();
           }
        }

        return $this->render('reset', [
            'model' => $model,
        ]);
    }
	
	public function actionLink() {

		return $this->render('link');

	}

	public function actionDisconnect() {

		$client = $_POST['client'];

		$auth = Auth::find()->where(['uid' => Yii::$app->user->id, 'source' => $client])->one();

		if ($auth) {

			if ($auth->delete()) {

				\Yii::$app->session->setFlash(

					'success',

					'Successfully disconnect account'

				);

			} else {

				\Yii::$app->session->setFlash(

					'error',

					'Failed to disconnect account: '.json_encode($auth->getErrors())

				);

			}

		} else {

			\Yii::$app->session->setFlash(

				'error',

				'Cannot find linked account.'

			);

		}

		

		return $this->redirect(['users/link']);

		Yii::$app->end();

	}
}