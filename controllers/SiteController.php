<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Url;
use app\models\UrlLog;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGenerate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $url = Yii::$app->request->post('url');

        // 1. Валидация формата URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'Неверный формат URL (должен начинаться с http:// или https://)'];
        }

        // 2. Проверка доступности ресурса (Ping/HEAD request)
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true); // Нам не нужно тело, только заголовки
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Таймаут 5 секунд
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Игнорируем SSL ошибки для проверки доступности
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || $httpCode === 0) {
            return ['success' => false, 'message' => 'Данный URL не доступен или сервер не отвечает.'];
        }

        // 3. Сохранение в базу
        $model = new Url();
        $model->original_url = $url;
        $model->created_at = time();
        
        // Генерация уникального короткого кода
        do {
            $model->short_code = Yii::$app->security->generateRandomString(6);
        } while (Url::findOne(['short_code' => $model->short_code]));

        if ($model->save()) {
            // 4. Генерация QR кода
            // Ссылка на редирект внутри нашего приложения
            $redirectUrl = Yii::$app->urlManager->createAbsoluteUrl(['site/redirect', 'code' => $model->short_code]);

            $result = Builder::create()
                ->data($redirectUrl)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(300)
                ->margin(10)
                ->build();

            // Получаем QR в формате Data URI (base64), чтобы отдать в AJAX
            $qrCodeDataUri = $result->getString(); 

            return [
                'success' => true,
                'short_url' => $redirectUrl,
                'qr_code' => $qrCodeDataUri,
                'original' => $url
            ];
        } else {
            return ['success' => false, 'message' => 'Ошибка сохранения в базу данных.'];
        }
    }

    // Контроллер перехода по ссылке
    public function actionRedirect($code)
    {
        $model = Url::findOne(['short_code' => $code]);

        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Ссылка не найдена.');
        }

        // Логирование перехода
        $log = new UrlLog();
        $log->url_id = $model->id;
        $log->ip_address = Yii::$app->request->userIP;
        $log->user_agent = Yii::$app->request->userAgent;
        $log->created_at = time();
        $log->save();

        // Увеличение счетчика
        $model->clicks++;
        $model->save(false);

        // Редирект
        return Yii::$app->response->redirect($model->original_url);
    }
}