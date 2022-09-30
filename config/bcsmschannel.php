<?php

use Swisssms\Drivers\BulkSMSNigeriaDotCom;
use Swisssms\Drivers\NigeriaBulkSmsDotCom;
use Swisssms\Drivers\SmsBroadcastDotComDotAuApi;
use Swisssms\Drivers\TermiiSms;
use Swisssms\Drivers\TwilioSms;
use Swisssms\Drivers\VonageSms;

return [
    'sender'        => env('BULKSMS_SENDER'),
    'is_enabled'    => env('BULKSMS_ENABLED'),
    'mock_type'     => env('BULKSMS_MOCK_TYPE', 'log'),
    'mock_to'       => env('BULKSMS_MOCK_TO', 'mock@bcsms_example.com'),

    'default_driver'    => 'termii',
    'drivers'   =>  [
        'nigeriabulksmsdotcom'  =>  [
            'class' =>  NigeriaBulkSmsDotCom::class,
            'auth'  =>  [
                'username'  =>  env('NBSMS_DOTCOM_USERNAME'),
                'password'  =>  env('NBSMS_DOTCOM_PASSWORD'),
            ],
            'url'   =>  env('NBSMS_DOTCOM_URL')
        ],

        'smsbroadcast'  =>  [
            'class' =>  SmsBroadcastDotComDotAuApi::class,
            'auth'  =>  [
                'username'  =>  env('SMSBRC_DOTCOM_DOT_AU_USERNAME'),
                'password'  =>  env('SMSBRC_DOTCOM_DOT_AU_PASSWORD'),
            ],
            'url'   =>  env('SMSBRC_DOTCOM_DOT_AU_URL')
        ],

        'bulksmsnigeriadotcom'  =>  [
            'class' =>  BulkSMSNigeriaDotCom::class,
            'auth'  =>  [
                'api_key'  =>  env('BSMSN_DOTCOM_API_KEY')
            ],
            'url'   =>  env('BSMSN_DOTCOM_URL')
        ],

        'vonagesms' =>  [
            'class' =>  VonageSms::class,
            'auth'  =>  [
                'api_key'       =>  '',
                'api_secret'    =>  ''
            ]
        ],

        'twiliosms' =>  [
            'class' =>  TwilioSms::class,
            'auth'  =>  [
                'sid'   =>  '',
                'api_secret'    =>  ''
            ]
        ],

        'termii'    =>  [
            'class' =>  TermiiSms::class,
            'sender_id' =>  'N-Alert',
            'channel'   =>  'dnd',
            'auth'  =>  [
                'api_key'   =>  env('TERMII_API_KEY'),
                'api'
            ]
        ]
    ],
];
