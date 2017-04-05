<?php
namespace PatrickBroens\Seo\Frontend\Page;

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
use TYPO3\CMS\Frontend\ContentObject\ContentContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * SEO preview
 */
class PreviewGenerator
{
    /**
     * The content object renderer
     *
     * @var ContentObjectRenderer
     */
    protected $contentObjectRenderer;

    /**
     * Constructor
     *
     * Set the content object renderer
     */
    public function __construct()
    {
        $this->contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * Generate the preview
     *
     * @return string
     */
    public function generatePreview(): string
    {
        return json_encode((object)[
            'configuration' => $this->getConfiguration(),
            'meta' => $this->getMetadata(),
            'content' => $this->getContent()
        ]);
    }

    /**
     * Get the configuration
     *
     * @return \stdClass
     */
    protected function getConfiguration(): \stdClass
    {
        return (object)[
            'pageTitleOverride' => $this->hasPageTitleOverride(),
            'pageTitle' => htmlspecialchars($this->getTypoScriptFrontendController()->page['title']),
            'siteTitle' => htmlspecialchars(trim($this->getTypoScriptFrontendController()->tmpl->setup['sitetitle'])),
            'pageTitleSeparator' => htmlspecialchars($this->getPageTitleSeparator()),
            'pageTitleFirst' => (bool)$this->getTypoScriptFrontendController()->config['config']['pageTitleFirst'],
            'noPageTitle' => (int)$this->getTypoScriptFrontendController()->config['config']['noPageTitle']
        ];
    }

    /**
     * Get the metadata
     *
     * @return \stdClass
     */
    protected function getMetaData(): \stdClass
    {
        return (object)[
            'url' => htmlspecialchars($this->getUrl()),
            'title' => htmlspecialchars($this->getPageTitle()),
            'browserTitle' => htmlspecialchars($this->getTypoScriptFrontendController()->page['seo_browser_title']),
            'description' => htmlspecialchars($this->getTypoScriptFrontendController()->page['description']),
            'locale' => htmlspecialchars($this->getTypoScriptFrontendController()->config['config']['locale_all'])
        ];
    }

    /**
     * Get the page content
     *
     * @return string
     */
    protected function getContent(): string
    {
        $contentContentObject = GeneralUtility::makeInstance(ContentContentObject::class, $this->contentObjectRenderer);

        return preg_replace('/([\r\n\t])/','', $contentContentObject->render(['table' => 'tt_content']));
    }

    /**
     * Get the url for this page
     *
     * @return string
     */
    protected function getUrl(): string
    {
        return $this->contentObjectRenderer->typoLink_URL(
            [
                'parameter' => '#',
                'forceAbsoluteUrl' => true,
                'useCacheHash' => true
            ]
        );
    }

    /**
     * Generate title for page
     *
     * @return string
     */
    protected function getPageTitle(): string
    {
        $pageTitle = '';

        if (!$this->isPageTitleDisabled()) {

            $pageTitleSeparator = $this->getPageTitleSeparator();

            $pageTitle = $this->getTypoScriptFrontendController()->tmpl->printTitle(
                $this->getTypoScriptFrontendController()->altPageTitle ?: $this->getTypoScriptFrontendController()->page['title'],
                $this->getTypoScriptFrontendController()->config['config']['noPageTitle'],
                $this->getTypoScriptFrontendController()->config['config']['pageTitleFirst'],
                $pageTitleSeparator
            );
        }

        return $pageTitle;
    }

    /**
     * Checks if the page title is manipulated by titleTagFunction or stdWrap
     *
     * @return bool
     */
    protected function hasPageTitleOverride(): bool
    {
        $hasPageTitleOverride = false;

        if (
            $this->getTypoScriptFrontendController()->config['config']['titleTagFunction']
            || (
                isset($this->getTypoScriptFrontendController()->config['config']['pageTitle.'])
                && is_array($this->getTypoScriptFrontendController()->config['config']['pageTitle.'])
            )
            || $this->isPageTitleDisabled()
        ) {
            $hasPageTitleOverride = true;
        }

        return $hasPageTitleOverride;
    }

    /**
     * Generates the page title separator
     *
     * Checks for a custom pageTitleSeparator, and perform stdWrap on it
     *
     * @return string
     * @todo Should go in TYPO3\CMS\Frontend\Page\PageGenerator and split up method generatePageTitle()
     */
    protected function getPageTitleSeparator(): string
    {
        $pageTitleSeparator = ':';

        if (
            isset($this->getTypoScriptFrontendController()->config['config']['pageTitleSeparator'])
            && $this->getTypoScriptFrontendController()->config['config']['pageTitleSeparator'] !== ''
        ) {
            $pageTitleSeparator = $this->getTypoScriptFrontendController()->config['config']['pageTitleSeparator'];

            if (
                isset($this->getTypoScriptFrontendController()->config['config']['pageTitleSeparator.'])
                && is_array($this->getTypoScriptFrontendController()->config['config']['pageTitleSeparator.'])
            ) {
                $pageTitleSeparator = $this->getTypoScriptFrontendController()->cObj->stdWrap(
                    $pageTitleSeparator,
                    $this->getTypoScriptFrontendController()->config['config']['pageTitleSeparator.']
                );
            } else {
                $pageTitleSeparator .= ' ';
            }
        }

        return $pageTitleSeparator;
    }

    /**
     * Returns true if page title is disabled
     *
     * @return bool
     */
    protected function isPageTitleDisabled(): bool
    {
        return (int)$this->getTypoScriptFrontendController()->config['config']['noPageTitle'] === \TYPO3\CMS\Frontend\Page\PageGenerator::NO_PAGE_TITLE;
    }

    /**
     * Get the TypoScript frontend controller
     *
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
