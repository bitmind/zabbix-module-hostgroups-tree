<?php declare(strict_types = 0);

/**
 * Host group tree widget form view.
 *
 * @var CView $this
 * @var array $data
 */

(new CWidgetFormView($data))
	->addFieldset(
		(new CWidgetFormFieldsetCollapsibleView(_('Filters')))
			->setExpanded()
			->addField(array_key_exists('groupids', $data['fields'])
				? new CWidgetFieldMultiSelectGroupView($data['fields']['groupids'])
				: null
			)
			->addField(array_key_exists('exclude_groupids', $data['fields'])
				? new CWidgetFieldMultiSelectGroupView($data['fields']['exclude_groupids'])
				: null
			)
			->addField(
				new CWidgetFieldSeveritiesView($data['fields']['severities'])
			)
	)
	->addFieldset(
		(new CWidgetFormFieldsetCollapsibleView(_('Display options')))
			->setExpanded()
			->addField(
				new CWidgetFieldRadioButtonListView($data['fields']['problems'])
			)
			->addField(
				new CWidgetFieldCheckBoxView($data['fields']['show_suppressed'])
			)
			->addField(
				new CWidgetFieldCheckBoxView($data['fields']['show_hosts'])
			)
	)
	->show();
