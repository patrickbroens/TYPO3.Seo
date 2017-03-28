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
 * Module: PatrickBroens/Seo/FormEngine/SeoHinting
 * Contains all JS functions related to TYPO3 TCEforms/SeoHinting
 * @internal
 */
define(['jquery', 'TYPO3/CMS/Backend/FormEngineValidation'], function ($, FormEngineValidation) {

	/**
	 * The main SeoHinting object
	 *
	 * @type {{rulesSelector: string, inputSelector: string, markerSelector: string, fieldHintClass: string, tabHintClass: string}}
	 * @exports PatrickBroens/Seo/FormEngine/SeoHinting
	 */
	var SeoHinting = {
		rulesSelector: '[data-formengine-seo-rules]',
		inputSelector: '[data-formengine-seo-params]',
		markerSelector: '.t3js-formengine-validation-marker',
		fieldHintClass: 'has-hint',
		tabHintClass: 'has-seo-hint'
	};

	/**
	 * Initialize SEO hinting for the first time
	 */
	SeoHinting.initialize = function() {
		$(document).find('.' + SeoHinting.fieldHintClass).removeClass(SeoHinting.fieldHintClass);

		// Initialize input fields
		SeoHinting.initializeInputFields().promise().done(function () {
			// Bind to field changes
			$(document).on('change', SeoHinting.rulesSelector, function() {
				SeoHinting.validate();
			});
		});

		SeoHinting.validate();
	};

	/**
	 * Initialize all input fields
	 *
	 * @returns {Object}
	 */
	SeoHinting.initializeInputFields = function() {
		return $(document).find(SeoHinting.inputSelector).each(function() {
			var config = $(this).data('formengine-seo-params'),
				fieldName = config.field,
				$field = $('[name="' + fieldName + '"]');

			// ignore fields which already have been initialized
			if ($field.data('main-field') === undefined) {
				$field.data('main-field', fieldName);
				$field.data('config', config);
				SeoHinting.initializeInputField(fieldName);
			}
		});
	};

	/**
	 * Initialize field by name
	 *
	 * @param {String} fieldName
	 */
	SeoHinting.initializeInputField = function(fieldName) {
		var $field = $('[name="' + fieldName + '"]'),
			$humanReadableField = $('[data-formengine-input-name="' + fieldName + '"]'),
			$mainField = $('[name="' + $field.data('main-field') + '"]');

		if ($mainField.length === 0) {
			$mainField = $field;
		}

		var config = $mainField.data('config');

		$humanReadableField.data('main-field', fieldName);
		$humanReadableField.data('config', config);

		// append the counter only at focus to avoid cluttering the DOM
		$humanReadableField.on('focus', function() {
			var $field = $(this),
				$parent = $field.parents('.t3js-formengine-field-item:first'),
				rules = $field.data('formengine-seo-rules');

			$.each(rules, function(k, rule) {
				$parent.append($('<div />', {'class': 't3js-hint ' + rule.class}).append($('<span />')));
			});

			SeoHinting.validate();
		}).on('keyup',
			SeoHinting.validate
		).on('blur', function(e) {
			var $parent = $field.parents('.t3js-formengine-field-item:first');
			$parent.find('.t3js-hint').remove();
		});
	};

	/**
	 * Run validation for field
	 *
	 * @param {Object} $field
	 * @param {String} [value=$field.val()]
	 * @returns {String}
	 */
	SeoHinting.validateField = function($field, value) {
		value = value || $field.val() || '';

		var rules = $field.data('formengine-seo-rules'),
			markParent = false,
			returnValue = value,
			totalCharacters = SeoHinting.getCharacterCount($field),
			$parent = $field.parents('.t3js-formengine-field-item:first');

		if (!$.isArray(value)) {
			value = FormEngineValidation.ltrim(value);
		}

		$.each(rules, function(k, rule) {
			var labelClass = 'label-success',
				threshold = 15;

			switch (rule.type) {
				case 'charCountRange':
					if (
						totalCharacters > rule.max
						|| totalCharacters < rule.min
					) {
						markParent = true;
						if (
							totalCharacters - rule.max > threshold
							|| rule.min - totalCharacters > threshold
						) {
							labelClass = 'label-danger';
						} else if (
							(
								totalCharacters > rule.max
								&& totalCharacters < rule.max + threshold
							) || (
								totalCharacters < rule.min
								&& totalCharacters > rule.min - threshold
							)
						) {
							labelClass = 'label-warning';
						}
					}

					$parent.find('.t3js-hint.' + rule.class + ' span')
						.removeClass()
						.addClass('label ' + labelClass)
						.text(TYPO3.lang['SeoHinting.characterCountRange']
							.replace(
								'{0}', rule.min
							).replace(
								'{1}', rule.max
							).replace(
								'{2}', totalCharacters
							)
						);
					break;
			}
		});

		if (markParent) {
			// mark field
			$field.closest(SeoHinting.markerSelector).addClass(SeoHinting.fieldHintClass);

			// check tabs
			SeoHinting.markParentTab($field);
		}
		return returnValue;
	};



	/**
	 * Validate the complete form
	 */
	SeoHinting.validate = function() {
		$(document).find(SeoHinting.markerSelector + ', .t3js-tabmenu-item')
			.removeClass(SeoHinting.tabHintClass)
			.removeClass('has-seo-hint');

		$(SeoHinting.rulesSelector).each(function() {
			var $field = $(this);
			if (!$field.closest('.t3js-flex-section-deleted, .t3js-inline-record-deleted').length) {
				var modified = false,
					currentValue = $field.val(),
					newValue = SeoHinting.validateField($field, currentValue);
				if ($.isArray(newValue) && $.isArray(currentValue)) {
					// handling for multi-selects
					if (newValue.length !== currentValue.length) {
						modified = true;
					} else {
						for (var i = 0; i < newValue.length; i++) {
							if (newValue[i] !== currentValue[i]) {
								modified = true;
								break;
							}
						}
					}
				} else if (newValue.length && currentValue !== newValue) {
					modified = true;
				}
				if (modified) {
					$field.val(newValue);
				}
			}
		});
	};

	/**
	 * Find tab by field and mark it as with class
	 *
	 * @param {Object} $element
	 */
	SeoHinting.markParentTab = function($element) {
		var $panes = $element.parents('.tab-pane');
		$panes.each(function() {
			var $pane = $(this),
				id = $pane.attr('id');
			$(document)
				.find('a[href="#' + id + '"]')
				.closest('.t3js-tabmenu-item')
				.addClass(SeoHinting.tabHintClass);
		});
	};

	/**
	 * Get the character count of a field
	 *
	 * @param {Object} $field
	 * @returns {Integer}
	 */
	SeoHinting.getCharacterCount = function($field) {
		var fieldText = $field.val();

		return fieldText.length;
	};

	SeoHinting.initialize();

	TYPO3.SeoHinting = SeoHinting;

	return SeoHinting;
});
