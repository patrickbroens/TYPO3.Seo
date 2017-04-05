<?php
defined('TYPO3_MODE') or die();

// Define columns which need to be changed
$changedColumns = [
    'columns' => [
        'description' => [
            'config' => [
                'renderType' => 'SeoDescriptionElement',
                'hints' => [
                    'charCount' => [
                        'max' => 157
                    ]
                ]
            ]
        ]
    ]
];

// Define new columns
$newColumns = [
    'seo_browser_title' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.browserTitle',
        'config' => [
            'type' => 'input',
            'renderType' => 'SeoBrowserTitleElement',
            'size' => 255,
            'eval' => 'trim',
            'placeholderField' => 'title',
            'hints' => [
                // 'progress' => []
            ]
        ]
    ],
    'seo_focus_keyword' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.focusKeyword',
        'config' => [
            'type' => 'input',
            'renderType' => 'SeoFocusKeywordElement',
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
            'renderType' => 'SeoYoastElement',
        ]
    ]
];

foreach (['pages', 'pages_language_overlay'] as $tableName) {
    // Change the description field
    $GLOBALS['TCA'][$tableName] = array_replace_recursive($GLOBALS['TCA'][$tableName], $changedColumns);

    // Delete (replace) the description field in the metatags palette
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        $tableName,
        'metatags',
        '--linebreak--',
        'replace:description'
    );

    // Add the new fields to the table TCA
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($tableName, $newColumns);

    // Add the "seo" tab to the table TCA for page types "Standard" (1) and "Shortcut" (4)
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




