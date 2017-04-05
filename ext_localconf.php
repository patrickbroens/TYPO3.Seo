<?php
defined('TYPO3_MODE') or die();

// Register the SEO node types
$nodeTypes = [
    'SeoBrowserTitleElement' => [
        1490866610,
        \PatrickBroens\Seo\Backend\Form\Element\BrowserTitleElement::class
    ],
    'SeoFocusKeywordElement' => [
        1490613007,
        \PatrickBroens\Seo\Backend\Form\Element\FocusKeywordElement::class
    ],
    'SeoDescriptionElement' => [
        1490716431,
        \PatrickBroens\Seo\Backend\Form\Element\DescriptionElement::class
    ],
    'SeoYoastElement' => [
        1490777645,
        \PatrickBroens\Seo\Backend\Form\Element\YoastElement::class
    ]

];

foreach ($nodeTypes as $nodeName => $nodeType) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$nodeType[0]] = [
        'nodeName' => $nodeName,
        'priority' => 40,
        'class' => $nodeType[1]
    ];
}

// Add TypoScript constant value for the frontend preview type (typeNum)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
    'config.seo.frontend_preview_type = '
        . \PatrickBroens\Seo\Backend\Form\Element\YoastElement::FRONTEND_PREVIEW_TYPE
);

// Add the TypoScript setup for the frontend preview rendering
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'Seo',
    'setup',
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:seo/Configuration/TypoScript/setup.txt">',
    'defaultContentRendering'
);
