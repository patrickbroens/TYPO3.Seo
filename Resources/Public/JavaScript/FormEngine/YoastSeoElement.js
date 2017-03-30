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

/**
 * Module: TYPO3/CMS/Seo/FormEngine/YoastSeoElement
 * Yoast SEO element
 * @internal
 */

define([
	'jquery',
	'TYPO3/CMS/Seo/Yoast/bundle',
	'TYPO3/CMS/Backend/AjaxDataHandler',
	'TYPO3/CMS/Backend/Notification'
], function ($,
	YoastSEO,
	AjaxDataHandler,
	Notification
) {
	'use strict';

	var YoastSeoElement = {
		targetElement: $('#' + TYPO3.settings.YoastSeo.targetElementId),
		previewRequest:  $.get(TYPO3.settings.YoastSeo.previewDataUrl)
	};

	// make sure the document is ready before we interact with the DOM
	// use the jQuery (ready) callback
	YoastSeoElement.initialize = function() {
		YoastSeoElement.previewRequest
			.done(function (previewDocument) {
				var $snippetPreviewContainer = YoastSeoElement.buildSnippetPreviewContainer();
				$snippetPreviewContainer.attr('id', 'snippet');

				var app = YoastSeoElement.getApplication(previewDocument, $snippetPreviewContainer);

				YoastSeoElement.modifySnippetPreviewContainer($snippetPreviewContainer);

				app.refresh();

				YoastSeoElement.initializeFieldSynchronization(app);
			})
			.fail(function (jqXHR) {
				Notification.error('Loading the page content preview failed', [jqXHR.status, jqXHR.statusText].join(' '), 0);
			});
	};

	YoastSeoElement.buildSnippetPreviewContainer = function() {
		var $snippetPreviewContainer = YoastSeoElement.targetElement.append('<div class="snippetPreview" />').find('.snippetPreview');
		$snippetPreviewContainer.attr('id', 'snippet');

		return $snippetPreviewContainer;
	};

	YoastSeoElement.getApplication = function(previewDocument, $snippetPreviewContainer) {
		var $previewDocument = $(previewDocument),
			$metaSection = $previewDocument.find('meta'),
			$contentElements = $previewDocument.find('content>element'),
			pageContent = '',
			$readabilityPanel = null,
			$seoPanel = null,
			$targetPanels = YoastSeoElement.targetElement.append('<div class="row" />').find('.row');

		if (YoastSeoElement.targetElement.hasClass('yoastSeo--small')) {
			$readabilityPanel = $targetPanels.append(YoastSeoElement.buildYoastPanelMarkup(TYPO3.settings.YoastSeo.targetElementId, 'readability')).find('.readabilityPanel');
			$seoPanel = $targetPanels.append(YoastSeoElement.buildYoastPanelMarkup(TYPO3.settings.YoastSeo.targetElementId, 'seo')).find('.seoPanel');
		} else {
			$readabilityPanel = YoastSeoElement.targetElement.append(YoastSeoElement.buildYoastPanelMarkup(TYPO3.settings.YoastSeo.targetElementId, 'readability')).find('.readabilityPanel');
			$seoPanel = YoastSeoElement.targetElement.append(YoastSeoElement.buildYoastPanelMarkup(TYPO3.settings.YoastSeo.targetElementId, 'seo')).find('.seoPanel');
		}

		$contentElements.each(function (index, element) {
			pageContent += element.textContent;
		});

		var app = new YoastSEO.App({
			snippetPreview: YoastSeoElement.getSnippetPreview($metaSection, $snippetPreviewContainer),
			targets: {
				output: $seoPanel.find('[data-panel-content]').attr('id'),
				contentOutput: $readabilityPanel.find('[data-panel-content]').attr('id')
			},
			callbacks: {
				getData: function () {
					return {
						title: $metaSection.find('title').text(),
						keyword: TYPO3.settings.YoastSeo.focusKeyword,
						text: pageContent
					};
				},
				bindElementEvents: function (app) {
				},
				saveScores: function (score) {
					$seoPanel.find('.wpseo-score-icon').first().addClass(YoastSEO.scoreToRating(score / 10));
				},
				saveContentScore: function (score) {
					$readabilityPanel.find('.wpseo-score-icon').first().addClass(YoastSEO.scoreToRating(score / 10));
				}
			},
			locale: $metaSection.find('locale').text(),
			translations: (window.tx_yoast_seo !== undefined && window.tx_yoast_seo !== null && window.tx_yoast_seo.translations !== undefined ? window.tx_yoast_seo.translations : null)
		});

		$readabilityPanel.find('[data-panel-title]').text((app.i18n.dgettext('js-text-analysis', 'Readability')));
		$seoPanel.find('[data-panel-title]').text((app.i18n.dgettext('js-text-analysis', 'Focus keyword')));
		$seoPanel.find('[data-panel-focus-keyword]').text(TYPO3.settings.YoastSeo.focusKeyword);

		// bind a click handler to the chevron icon of both panels
		YoastSeoElement.targetElement.not('.yoastSeo--small').find('.snippet-editor__heading').on('click', function () {
			var $panel = $(this).parent();
			$panel.find('.fa-chevron-down, .fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
			$panel.find('.snippet-editor__heading').toggleClass('snippet-editor__heading--active');
			$panel.find('[data-panel-content]').toggleClass('yoastPanel__content--open');
		});

		return app;
	};

	YoastSeoElement.getSnippetPreview = function($metaSection, $snippetPreview) {
		return new YoastSEO.SnippetPreview({
			data: {
				title: $metaSection.find('title').text(),
				metaDesc: $metaSection.find('description').text()
			},
			baseURL: $metaSection.find('url').text(),
			placeholder: {
				urlPath: ''
			},
			targetElement: $snippetPreview.get(0),
			callbacks: {}
		});
	};

	YoastSeoElement.buildYoastPanelMarkup = function(elementIdPrefix, type) {
		var focusKeyword = '';

		if (type === 'seo') {
			focusKeyword = '<span class="yoastPanel__focusKeyword" data-panel-focus-keyword></span>';
		}

		return '<div id="' + elementIdPrefix + '_' + type + '_panel" class="yoastPanel ' + type + 'Panel">'
			+ '<h3 class="snippet-editor__heading" data-controls="' + type + '">'
			+ '<span class="wpseo-score-icon"></span>'
			+ '<span class="yoastPanel__title" data-panel-title>' + type + '</span>'
			+ focusKeyword
			+ '<span class="fa fa-chevron-down"></span>'
			+ '</h3>'
			+ '<div id="' + elementIdPrefix + '_' + type + '_panel_content" data-panel-content class="yoastPanel__content"></div>'
			+ '</div>';
	};

	YoastSeoElement.modifySnippetPreviewContainer = function($snippetPreview) {
		$snippetPreview.find('.snippet-editor__label').each(function () {
			var $inputField = $(this).find('.snippet-editor__input').detach();

			$inputField.addClass('form-control').removeClass('snippet-editor__input');
			$inputField.attr('name', 'tx_yoastseo_help_yoastseoseoplugin[' + $inputField.attr('id') + ']');

			$(this).wrap('<div class="form-group"></div>');
			$(this).removeClass('snippet-editor__label');
			$(this).after($inputField);
		});

		$snippetPreview.find('.snippet-editor__progress').each(function () {
			var $prev = $(this).prev();
			$(this).appendTo($prev);
		});
	};

	YoastSeoElement.initializeFieldSynchronization = function(app) {
		var snippetPreview = app.snippetPreview,
			$progressBar = $(snippetPreview.element.progress.title),
			$progressClone = null;

		$.each(TYPO3.settings.YoastSeo.fields, function(fieldName, functionName) {
			var $typo3Field = $('[data-formengine-input-name*=\'data[pages][' + TYPO3.settings.YoastSeo.pageId + '][' + fieldName + ']\']');

			if (fieldName === 'seo_browser_title') {
				$typo3Field.on('focus', function () {
					$progressClone = $progressBar.clone().insertAfter($typo3Field.parent());
				});
				$typo3Field.on('blur', function () {
					$progressClone.remove();
				});
			}

			$typo3Field.on('keyup', function() {
				snippetPreview[functionName]($typo3Field.val());
				if (fieldName === 'seo_browser_title') {
					$progressClone.val($progressBar.val()).attr('class', $progressBar.attr('class'));
				}
			});
		});
	};





	YoastSeoElement.initialize();

	TYPO3.YoastSeoElement = YoastSeoElement;

	return YoastSeoElement;
});