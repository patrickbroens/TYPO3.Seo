<?php
defined('TYPO3_MODE') or die();

$changedColumns = [
    'columns' => [
        'description' => [
            'config' => [
                'renderType' => 'TextHintElement',
                'hints' => [
                    'charCount' => [
                        'max' => 157
                    ]
                ]
            ]
        ]
    ]
];

$GLOBALS['TCA']['pages'] = array_replace_recursive($GLOBALS['TCA']['pages'], $changedColumns);

$newColumns = [
    'seo_browser_title' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.browserTitle',
        'config' => [
            'type' => 'input',
            'size' => 255,
            'eval' => 'trim'
        ]
    ],
    'seo_focus_keyword' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.focusKeyword',
        'config' => [
            'type' => 'input',
            'renderType' => 'inputTextHintElement',
            'size' => 150,
            'hints' => [
                'required' => []
            ]
        ]
    ],
    'seo_preview' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.preview',
        'config' => [
            'type' => 'none',
            'renderType' => 'YoastSeoElement',
        ]
    ]
];

foreach (['pages', 'pages_language_overlay'] as $tableName) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($tableName, $newColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        $tableName,
        'metatags',
        '--linebreak--',
        'replace:description'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        $tableName,
        'seo',
        '
            description,
            seo_browser_title, 
            seo_focus_keyword, 
            seo_preview
        '
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        $tableName,
        '
           --div--;LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:tab.seo,
           seo_preview,
           seo_browser_title,
           seo_focus_keyword,
           description
        ',
        '1,4'
    );
}




