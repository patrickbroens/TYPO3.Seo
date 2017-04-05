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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Browser title element
 */
class BrowserTitleElement extends AbstractHintElement
{
    /**
     * Render the input text hint element
     *
     * @return array
     */
    public function render(): array
    {
        $row = $this->data['databaseRow'];
        $parameterArray = $this->data['parameterArray'];

        $itemValue = $parameterArray['itemFormElValue'];
        $configuration = $parameterArray['fieldConf']['config'];
        $evalList = GeneralUtility::trimExplode(',', $configuration['eval'], true);
        $placeholderField = trim($configuration['placeholderField']);
        $size = MathUtility::forceIntegerInRange(
            $configuration['size'] ?? $this->defaultInputWidth,
            $this->minimumInputWidth,
            $this->maxInputWidth
        );
        $width = (int)$this->formMaxWidth($size);

        $resultArray = $this->initializeResultArray();

        // @todo: The whole eval handling is a mess and needs refactoring
        foreach ($evalList as $func) {
            // @todo: This is ugly: The code should find out on it's own whether a eval definition is a
            // @todo: keyword like "date", or a class reference. The global registration could be dropped then
            // Pair hook to the one in \TYPO3\CMS\Core\DataHandling\DataHandler::checkValue_input_Eval()
            if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][$func])) {
                if (class_exists($func)) {
                    $evalObj = GeneralUtility::makeInstance($func);
                    if (method_exists($evalObj, 'deevaluateFieldValue')) {
                        $_params = [
                            'value' => $itemValue
                        ];
                        $itemValue = $evalObj->deevaluateFieldValue($_params);
                    }
                    if (method_exists($evalObj, 'returnFieldJS')) {
                        $resultArray['additionalJavaScriptPost'][] = 'TBE_EDITOR.customEvalFunctions[' . GeneralUtility::quoteJSvalue($func) . ']'
                            . ' = function(value) {' . $evalObj->returnFieldJS() . '};';
                    }
                }
            }
        }

        $fieldInformationResult = $this->renderFieldInformation();
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $fieldControlResult = $this->renderFieldControl();
        $fieldControlHtml = $fieldControlResult['html'];

        $attributes = [
            'value' => '',
            'id' => StringUtility::getUniqueId('formengine-input-'),
            'class' => implode(' ', [
                'form-control',
                't3js-clearable',
                'hasDefaultValue',
            ]),
            'data-formengine-seo-rules' => $this->getHintingDataAsJsonString($configuration['hints']),
            'data-formengine-seo-params' => json_encode([
                'field' => $parameterArray['itemFormElName'],
            ]),
            'data-formengine-input-params' => json_encode([
                'field' => $parameterArray['itemFormElName'],
                'evalList' => implode(',', $evalList),
                'is_in' => ''
            ]),
            'data-formengine-input-name' => $parameterArray['itemFormElName'],
        ];

        $html = [];
        $html[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $html[] =  '<div class="form-wizards-wrap">';
        $html[] =      '<div class="form-wizards-element">';
        $html[] =          '<input type="text"' . GeneralUtility::implodeAttributes($attributes, true) . ' placeholder="' . $row[$placeholderField] . '" />';
        $html[] =          '<input type="hidden" name="' . $parameterArray['itemFormElName'] . '" value="' . htmlspecialchars($itemValue) . '" />';
        $html[] =      '</div>';
        $html[] =      '<div class="form-wizards-items-aside">';
        $html[] =          '<div class="btn-group">';
        $html[] =              $fieldControlHtml;
        $html[] =          '</div>';
        $html[] =      '</div>';
        $html[] =      '<div class="form-wizards-items-bottom">';
        $html[] =          $fieldWizardHtml;
        $html[] =      '</div>';
        $html[] =  '</div>';
        $html[] = '</div>';
        $html = implode(LF, $html);

        $resultArray['html'] = '<div class="formengine-field-item t3js-formengine-field-item">' . $html . '</div>';
        $resultArray['requireJsModules'][] = 'TYPO3/CMS/Seo/FormEngine/SeoHinting';
        $resultArray['stylesheetFiles'][] = 'EXT:seo/Resources/Public/Css/SeoHinting.css';
        $resultArray['additionalInlineLanguageLabelFiles'][] = 'EXT:seo/Resources/Private/Language/Backend/Element/SeoHinting.xlf';

        return $resultArray;
    }
}
