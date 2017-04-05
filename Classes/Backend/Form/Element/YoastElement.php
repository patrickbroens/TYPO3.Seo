<?php
namespace PatrickBroens\Seo\Backend\Form\Element;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

/**
 * Yoast element
 *
 * Shows preview, readability and seo panel
 *
 * This element is solely a placeholder for javascript functionality
 */
class YoastElement extends AbstractFormElement
{
    /**
     * The column name for the focus keyword
     *
     * @var string
     */
    const FOCUS_KEYWORD_COLUMN_NAME = 'seo_focus_keyword';

    /**
     * The frontend preview type number
     *
     * @var int
     */
    const FRONTEND_PREVIEW_TYPE = 1490776755;

    /**
     * Render the input text hint element
     *
     * @return array
     */
    public function render(): array
    {
        $row = $this->data['databaseRow'];
        $table = $this->data['tableName'];
        $targetElementId = uniqid('_YoastSEO_panel_', false);

        $resultArray = $this->initializeResultArray();

        $fieldInformationResult = $this->renderFieldInformation();
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $html = [];
        $html[] = '<div id="' . $targetElementId . '">';
        $html[] = '<!-- ' . $targetElementId . ' -->';
        $html[] = '</div>';
        $html = implode(LF, $html);

        $resultArray['html'] = $html;
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/Seo/FormEngine/YoastSeoElement';
        $resultArray['additionalJavaScriptPost'][] = $this->getAdditionalJavaScript(
            $targetElementId,
            $table,
            $row
        );
        $resultArray['stylesheetFiles'][] = 'EXT:seo/Resources/Public/Css/Yoast/yoast-seo.min.css';
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:seo/Resources/Private/Language/Backend/Element/SeoHinting.xlf';
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:seo/Resources/Private/Language/Backend/Element/YoastSeoElement.xlf';

        return $resultArray;
    }

    /**
     * Get the additional JavaScript to initialize Yoast SEO preview snippet
     *
     * @param string $targetElementId The target element ID
     * @param string $table The table name
     * @param array $row The record row
     * @return string
     */
    protected function getAdditionalJavaScript(string $targetElementId, string $table, array $row): string
    {
        return 'TYPO3.settings.YoastSeo = '
            . json_encode(
                [
                    'focusKeyword' => $row[self::FOCUS_KEYWORD_COLUMN_NAME],
                    'previewDataUrl' => $this->getPreviewDataUrl($table, $row),
                    'targetElementId' => $targetElementId,
                    'pageId' => (int)$row['uid'],
                    'table' => $table,
                    'fields' => [
                        'seo_browser_title' => 'setTitle',
                        'description' => 'setMetaDescription'
                    ]
                ]
            )
            . ';';
    }

    /**
     * Get the preview data url
     *
     * @param string $table The table name
     * @param array $row The record row
     * @return string
     */
    protected function getPreviewDataUrl(string $table, array $row): string
    {
        return vsprintf(
            '/index.php?id=%d&type=%d&L=%d',
            [
                $this->getPageId($table, $row),
                self::FRONTEND_PREVIEW_TYPE,
                (int)$row['sys_language_uid']
            ]
        );
    }

    /**
     * Get the page ID
     *
     * Depending on table name
     *
     * @param string $table The table name
     * @param array $row The record row
     * @return int
     */
    protected function getPageId(string $table, array $row): int
    {
        return (int)(($table === 'pages') ? $row['uid'] : $row['pid']);
    }
}
