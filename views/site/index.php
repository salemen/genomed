<?php
/** @var yii\web\View $this */
$this->title = 'Сервис коротких ссылок + QR';
?>
<div class="site-index">
    <div class="jumbotron text-center bg-light mt-5 p-5 rounded shadow-sm">
        <h1 class="display-4">Сокращатель ссылок</h1>
        <p class="lead">Вставьте длинную ссылку, получите короткую и QR-код.</p>
    </div>

    <div class="body-content container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group mb-3">
                    <input type="text" id="urlInput" class="form-control form-control-lg" placeholder="https://example.com/very-long-url..." aria-label="URL">
                    <div class="input-group-append">
                        <button class="btn btn-primary btn-lg" type="button" id="generateBtn">ОК</button>
                    </div>
                </div>

                <!-- Блок ошибок -->
                <div id="errorBlock" class="alert alert-danger" style="display: none;"></div>

                <!-- Блок результата -->
                <div id="resultBlock" class="card mt-4" style="display: none;">
                    <div class="card-body text-center">
                        <h5 class="card-title">Ваша короткая ссылка:</h5>
                        <p>
                            <a href="#" id="shortLinkDisplay" target="_blank" class="font-weight-bold text-primary"></a>
                        </p>
                        <hr>
                        <h5 class="card-title">QR Код:</h5>
                        <img id="qrImage" src="" alt="QR Code" class="img-fluid mt-2" style="max-width: 200px;">
                        <p class="text-muted small mt-2">Наведите камеру телефона, чтобы открыть ссылку</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<< JS
$(document).ready(function() {
    $('#generateBtn').click(function() {
        var url = $('#urlInput').val();
        var btn = $(this);
        var errorBlock = $('#errorBlock');
        var resultBlock = $('#resultBlock');

        // Сброс интерфейса
        errorBlock.hide();
        resultBlock.hide();
        btn.prop('disabled', true).text('Проверка...');

        $.ajax({
            url: '/site/generate', // Путь к экшену
            type: 'POST',
            data: { url: url },
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).text('ОК');
                
                if (response.success) {
                    $('#shortLinkDisplay').attr('href', response.short_url).text(response.short_url);
                    $('#qrImage').attr('src', response.qr_code);
                    resultBlock.fadeIn();
                } else {
                    errorBlock.text(response.message).fadeIn();
                }
            },
            error: function() {
                btn.prop('disabled', false).text('ОК');
                errorBlock.text('Произошла ошибка сервера. Попробуйте позже.').fadeIn();
            }
        });
    });
});
JS;
$this->registerJs($script);
?>