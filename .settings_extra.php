<?php
return [
    'exception_handling' => [
        'value' => [
            'log' => [
                'class_name' => '\\OTUSFileExceptionHandlerLog',
                'settings' => [
                    'file' => '/otus.log',
                    'log_size' => 1000000,
                ],
            ],
        ],
        'readonly' => false,
    ],
];