<?php
defined('TYPO3_MODE') or die();

// Register the hint node types
$nodeTypes = [
    'BrowserTitleElement' => [
        1490866610,
        \PatrickBroens\Seo\Backend\Form\Element\BrowserTitleElement::class
    ],
    'inputTextHintElement' => [
        1490613007,
        \PatrickBroens\Seo\Backend\Form\Element\InputTextHintElement::class
    ],
    'TextHintElement' => [
        1490716431,
        \PatrickBroens\Seo\Backend\Form\Element\TextHintElement::class
    ],
    'YoastSeoElement' => [
        1490777645,
        \PatrickBroens\Seo\Backend\Form\Element\YoastSeoElement::class
    ]

];

foreach ($nodeTypes as $nodeName => $nodeType) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$nodeType[0]] = [
        'nodeName' => $nodeName,
        'priority' => 40,
        'class' => $nodeType[1]
    ];
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
    'config.seo.frontend_preview_type = '
        . \PatrickBroens\Seo\Backend\Form\Element\YoastSeoElement::FRONTEND_PREVIEW_TYPE
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'Seo',
    'setup',
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:seo/Configuration/TypoScript/setup.txt">',
    'defaultContentRendering'
);
