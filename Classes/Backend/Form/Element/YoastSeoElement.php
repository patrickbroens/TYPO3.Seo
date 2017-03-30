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
 * Yoast SEO element
 */
class YoastSeoElement extends AbstractFormElement
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

        $targetElementId = uniqid('_YoastSEO_panel_', false);
        $pageId = (int)$row['uid'];
        $focusKeyword = $row[self::FOCUS_KEYWORD_COLUMN_NAME];
        $previewDataUrl = vsprintf(
            '/index.php?id=%d&type=%d&L=%d',
            [
                (int)$pageId,
                self::FRONTEND_PREVIEW_TYPE,
                0
            ]
        );

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
            $focusKeyword,
            $previewDataUrl,
            $targetElementId,
            $pageId
        );
        $resultArray['stylesheetFiles'][] = 'EXT:seo/Resources/Public/Css/Yoast/yoast-seo.min.css';
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:seo/Resources/Private/Language/Backend/Element/SeoHinting.xlf';

        return $resultArray;
    }

    /**
     * Get the additional JavaScript to initialize Yoast SEO preview snippet
     *
     * @param string $focusKeyword The focus keyword
     * @param string $previewDataUrl The preview data URL
     * @param string $targetElementId The target element ID
     * @param int $pageId The page ID
     * @return string
     */
    protected function getAdditionalJavaScript(
        string $focusKeyword,
        string $previewDataUrl,
        string $targetElementId,
        int $pageId
    ): string {
        return 'TYPO3.settings.YoastSeo = '
            . json_encode(
                [
                    'focusKeyword' => $focusKeyword,
                    'previewDataUrl' => $previewDataUrl,
                    'targetElementId' => $targetElementId,
                    'pageId' => $pageId,
                    'fields' => [
                        'seo_browser_title' => 'setTitle',
                        'description' => 'setMetaDescription'
                    ]
                ]
            )
            . ';';
    }
}
