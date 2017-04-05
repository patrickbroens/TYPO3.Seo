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
 * Abstract hint element
 */
abstract class AbstractHintElement extends AbstractFormElement
{
    /**
     * Build JSON string for SEO rules.
     *
     * @param array $hints
     * @return string
     */
    protected function getHintingDataAsJsonString(array $hints): string
    {
        $hintingRules = [];

        foreach ($hints as $name => $configuration) {
            switch ($name) {
                case 'charCount':
                    $hintingRules[] = $this->characterCountHint($configuration);
                    break;
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
     * Get the rule for the character count hint
     *
     * @param array $configuration The hint configuration
     * @return array
     */
    protected function characterCountHint(array $configuration): array
    {
        $configuration['max'] = (int)$configuration['max'] ?? 157;

        return [
            'type' => 'charCount',
            'class' => 'hint-charcount',
            'max' => (int)$configuration['max']
        ];
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
