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
 * Description element
 */
class DescriptionElement extends AbstractHintElement
{
    /**
     * The number of chars expected per row when the height of a text area field is
     * automatically calculated based on the number of characters found in the field content.
     *
     * @var int
     */
    protected $charactersPerRow = 40;

    /**
     * This will render a <textarea>
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render()
    {
        $parameterArray = $this->data['parameterArray'];

        $itemValue = $parameterArray['itemFormElValue'];
        $configuration = $parameterArray['fieldConf']['config'];
        $cols = MathUtility::forceIntegerInRange($configuration['cols'] ?: $this->defaultInputWidth, $this->minimumInputWidth, $this->maxInputWidth);
        $width = $this->formMaxWidth($cols);

        // Setting number of rows
        $rows = MathUtility::forceIntegerInRange($configuration['rows'] ?: 5, 1, 20);
        $originalRows = $rows;
        $itemFormElementValueLength = strlen($itemValue);
        if ($itemFormElementValueLength > $this->charactersPerRow * 2) {
            $rows = MathUtility::forceIntegerInRange(
                round($itemFormElementValueLength / $this->charactersPerRow),
                count(explode(LF, $itemValue)),
                20
            );
            if ($rows < $originalRows) {
                $rows = $originalRows;
            }
        }

        $resultArray = $this->initializeResultArray();

        $fieldInformationResult = $this->renderFieldInformation();
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $fieldControlResult = $this->renderFieldControl();
        $fieldControlHtml = $fieldControlResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldControlResult, false);

        $attributes = [
            'id' => StringUtility::getUniqueId('formengine-textarea-'),
            'name' => htmlspecialchars($parameterArray['itemFormElName']),
            'data-formengine-seo-rules' => $this->getHintingDataAsJsonString($configuration['hints']),
            'data-formengine-seo-params' => json_encode([
                'field' => $parameterArray['itemFormElName'],
            ]),
            'data-formengine-input-name' => htmlspecialchars($parameterArray['itemFormElName']),
            'rows' => $rows,
            'wrap' => $configuration['wrap'] ?: 'virtual',
            'onChange' => implode('', $parameterArray['fieldChangeFunc']),
            'class' => implode(' ', [
                'form-control',
                't3js-formengine-textarea',
                'formengine-textarea'
            ])
        ];

        $html = [];
        $html[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $html[] =  '<div class="form-wizards-wrap">';
        $html[] =      '<div class="form-wizards-element">';
        $html[] =          '<textarea ' . GeneralUtility::implodeAttributes($attributes, true) . '>' . htmlspecialchars($itemValue) . '</textarea>';
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
