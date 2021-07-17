<?php

namespace app\controllers;

use Yii;
use app\models\mahasiswa;
use app\models\MahasiswaSearch;
use app\models\Prodi;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\base\Model;
use app\models\UploadForm;
use yii\web\UploadedFile;

/**
 * MahasiswaController implements the CRUD actions for mahasiswa model.
 */
class MahasiswaController extends Controller
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
     * Lists all mahasiswa models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MahasiswaSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single mahasiswa model.
     * @param integer $id
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
     * Creates a new mahasiswa model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new mahasiswa();

        if ($model->load(Yii::$app->request->post())) {
            $model->tgl_lahir = \Yii::$app->formatter->asDate($model->tgl_lahir, "yyyy-MM-dd");
            $image = UploadedFile::getInstance($model, 'image');
            $model->img = $image->nama;

            $model->save();
            $image->saveAs(Yii::$app->basePath . '/web/upload/' . $image->nama);

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpload()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->imageFiles = UploadedFile::getInstances($model, 'imageFiles');
            if ($model->upload()) {
                // file is uploaded successfully
                return;
            }
        }

        return $this->render('upload', ['model' => $model]);
    }

    
    /**
     * Updates an existing mahasiswa model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $model->tgl_lahir = date('d-M-y', strtotime($model->tgl_lahir));
        if ($model->load(Yii::$app->request->post())) {
            $model->tgl_lahir = \Yii::$app->formatter->asDate($model->tgl_lahir, "yyyy-MM-dd");
            $oldimg = $model->img;
            if($oldimg != ""){
                unlink(Yii::$app->basePath . '/web/upload/' . $oldimg);
            }

            $image = UploadedFile::getInstance($model, 'image');
            $model->img = $image->nama;

            $model->save();
            $image->saveAs(Yii::$app->basePath . '/web/upload/' . $image->nama);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing mahasiswa model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id)->delete();

        unlink(Yii::$app->basePath . '/web/upload/' . $model->img);

        return $this->redirect(['index']);
    }

    public function actionSubcat()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $prodi = $parents[0];
                $out = Prodi::getProdiList($prodi);

                return ['output'=>$out,'selected'=>''];
            }
        }
        return ['output'=>'','selected'=>''];
    }

    /**
     * Finds the mahasiswa model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return mahasiswa the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = mahasiswa::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
