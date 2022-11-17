<?php

return [
    'service_manager' => [
        'factories' => [
            'Laminas\Db\Adapter\Adapter' => 'Laminas\Db\Adapter\AdapterServiceFactory',
        ],
        'aliases' => [
            'db' => 'Laminas\Db\Adapter\Adapter',
        ],
    ],
    'db' => [
        'driver'    => 'pdo_mysql',
        //'database' => 'pr_test',
        //'database'       => 'panta_rhei',
        'database' => 'vubib_1',
        'username'  => 'root',
        'password'  => '',
        'charset'  => 'utf8',

    ],
];
