<?php
namespace PatrickBroens\Seo\View\Wizard\Element;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Input text hint element
 */
class InputTextHintElement extends AbstractFormElement
{
    /**
     * Render the input text hint element
     *
     * @return array
     */
    public function render(): array
    {
        $parameterArray = $this->data['parameterArray'];

        $itemValue = $parameterArray['itemFormElValue'];
        $configuration = $parameterArray['fieldConf']['config'];
        $size = MathUtility::forceIntegerInRange(
            $configuration['size'] ?? $this->defaultInputWidth,
            $this->minimumInputWidth,
            $this->maxInputWidth
        );
        $width = (int)$this->formMaxWidth($size);

        $resultArray = $this->initializeResultArray();

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
            'data-formengine-input-name' => $parameterArray['itemFormElName'],
        ];

        $html = [];
        $html[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $html[] =  '<div class="form-wizards-wrap">';
        $html[] =      '<div class="form-wizards-element">';
        $html[] =          '<input type="text"' . GeneralUtility::implodeAttributes($attributes, true) . ' />';
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

    /**
     * Build JSON string for SEO rules.
     *
     * @param array $configuration
     * @return string
     */
    protected function getHintingDataAsJsonString(array $hints): string
    {
        $hintingRules = [];

        foreach ($hints as $name => $configuration) {
            switch ($name) {
                case 'charCountRange':
                    $hintingRules[] = $this->characterCountRangeHint($configuration);
                    break;
                case 'required':
                    $hintingRules[] = $this->requiredHint();
                    break;
            }
        }

        return json_encode($hintingRules);
    }

    /**
     * Get the rule for the character count range hint
     *
     * @param array $configuration The hint configuration
     * @return array
     */
    protected function characterCountRangeHint(array $configuration): array
    {
        $configuration['max'] = (int)$configuration['max'] ?? 57;
        $configuration['min'] = (int)$configuration['min'] ?? 40;

        return [
            'type' => 'charCountRange',
            'class' => 'hint-charcountrange',
            'max' => (int)$configuration['max'],
            'min' => (int)$configuration['min']
        ];
    }

    /**
     * Get the rule for the required hint
     *
     * @return array
     */
    protected function requiredHint(): array
    {
        return [
            'type' => 'required',
            'class' => 'hint-required'
        ];
    }
}
