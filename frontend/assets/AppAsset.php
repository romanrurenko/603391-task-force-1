<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/normalize.css',
        'css/style.css',
        'css/task-force.css',
    ];
    public $js = [
        'js/city-select.js',
        'js/notify.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
