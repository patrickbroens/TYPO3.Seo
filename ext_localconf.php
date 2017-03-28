<?php
defined('TYPO3_MODE') or die();

// Register the SEO node types
$nodeTypes = [
    'seoBrowserTitle' => [
        1490613007,
        \PatrickBroens\Seo\View\Wizard\Element\SeoBrowserTitleElement::class
    ]
];

foreach ($nodeTypes as $nodeName => $nodeType) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$nodeType[0]] = [
        'nodeName' => $nodeName,
        'priority' => 40,
        'class' => $nodeType[1]
    ];
}
