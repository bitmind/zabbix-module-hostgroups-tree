<?php declare(strict_types = 0);

namespace Modules\HostGroupTreeWidget\Includes;

use Zabbix\Widgets\CWidgetForm;

use Zabbix\Widgets\Fields\{
	CWidgetFieldCheckBox,
	CWidgetFieldMultiSelectGroup,
	CWidgetFieldRadioButtonList,
	CWidgetFieldSeverities
};

/**
 * Host group tree widget form.
 *
 * Filters: root host groups, excluded host groups, severities.
 * Display options: show problems (All / Unacknowledged / None), show suppressed problems, show number of hosts
 * (All / Enabled / None).
 */
class WidgetForm extends CWidgetForm {

	public const PROBLEMS_ALL = 0;
	public const PROBLEMS_UNACKNOWLEDGED = 1;
	public const PROBLEMS_NONE = 2;

	// "Show number of hosts". 0 = None, so values saved by the former checkbox (0 = unchecked) keep their meaning.
	public const HOSTS_NONE = 0;
	public const HOSTS_ALL = 1;
	public const HOSTS_ENABLED = 2;

	public function addFields(): self {
		return $this
			// Roots of the tree: these groups and their subgroups ("<name>/..."). Empty = all host groups.
			->addField($this->isTemplateDashboard()
				? null
				: new CWidgetFieldMultiSelectGroup('groupids', _('Host groups'))
			)
			// Removed from the tree together with their subgroups.
			->addField($this->isTemplateDashboard()
				? null
				: new CWidgetFieldMultiSelectGroup('exclude_groupids', _('Exclude host groups'))
			)
			// Problems counted in the tree. None checked = all severities.
			->addField(
				new CWidgetFieldSeverities('severities', _('Severity'))
			)
			->addField(
				(new CWidgetFieldRadioButtonList('problems', _('Show problems'), [
					self::PROBLEMS_ALL => _('All'),
					self::PROBLEMS_UNACKNOWLEDGED => _('Unacknowledged'),
					self::PROBLEMS_NONE => _('None')
				]))->setDefault(self::PROBLEMS_ALL)
			)
			->addField(
				new CWidgetFieldCheckBox('show_suppressed', _('Show suppressed problems'))
			)
			->addField(
				(new CWidgetFieldRadioButtonList('show_hosts', _('Show number of hosts'), [
					self::HOSTS_ALL => _('All'),
					self::HOSTS_ENABLED => _('Enabled'),
					self::HOSTS_NONE => _('None')
				]))->setDefault(self::HOSTS_NONE)
			);
	}
}
