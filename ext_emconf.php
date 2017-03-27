<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'SEO',
    'description' => 'SEO testing',
    'category' => 'plugin',
    'author' => 'Patrick Broens',
    'author_email' => 'patrick.broens@typo3.org',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '8.6.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.1.0-8.99.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ]
    ],
    'autoload' => [
        'psr-4' => ['PatrickBroens\\Seo\\' => 'Classes']
    ],
];
