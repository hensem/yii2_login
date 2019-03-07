<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$mailer = require __DIR__ . '/mailer.php';
$authClientCollection = require __DIR__ . '/authClientCollection.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
		'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'EDOxEmk0-zB6dfNvDTLLEFZE0GZYad21',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => $mailer,
		/*
		content of mailer.php
		
		return [
		   'class' => 'yii\swiftmailer\Mailer',
		   'useFileTransport' => false,
		   'transport' => [
			   'class' => 'Swift_SmtpTransport',
			   'host' => 'smtp.gmail.com',
			   'username' => your_gmail_id,
			   'password' => your_gmail_password,
			   'port' => '587',  //smtp port
			   'encryption' => 'tls',  //connection
		   ],
		];
		*/
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
		/* content of db.php
		return [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost:3307;dbname=login',
			'username' => '',
			'password' => '',
			'charset' => 'utf8',
		];
		*/
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Hide index.php
            'showScriptName' => false,
            // Use pretty URLs
            'enablePrettyUrl' => true,
            'rules' => [
                'gii' => 'gii',
                'gii/<controller:\w+>' => 'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
            ],
        ],
		'authClientCollection' => $authClientCollection,
		/*
		content of authClientCollection.php
		
		return [
			'class' => 'yii\authclient\Collection',
			'clients' => [
				'google' => [
					'class' => 'yii\authclient\clients\Google',
					'clientId' => your_clientId,
					'clientSecret' => your_clientSecret,
				],
				'facebook' => [
					'class' => 'yii\authclient\clients\Facebook',
					''lientId' => your_clientId,
					'clientSecret' => your_clientSecret,
				],
			],
		];
		*/
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
