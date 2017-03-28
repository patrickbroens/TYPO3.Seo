<?php
defined('TYPO3_MODE') or die();

$newColumns = [
    'seo_browser_title' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.browserTitle',
        'config' => [
            'type' => 'input',
            'renderType' => 'seoBrowserTitle',
            'min' => 40,
            'max' => 57
        ]
    ],
    'seo_focus_keyword' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.focusKeyword',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 30,
            'eval' => 'required'
        ]
    ],
    'seo_preview' => [
        'exclude' => false,
        'label' => 'LLL:EXT:seo/Resources/Private/Language/TCA/Pages.xlf:field.preview',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'max' => 30,
            'eval' => 'required'
        ]
    ]
];

foreach (['pages', 'pages_language_overlay'] as $tableName) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($tableName, $newColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        $tableName,
        'seo',
        '
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
           seo_focus_keyword
        ',
        '1,4'
    );
}




