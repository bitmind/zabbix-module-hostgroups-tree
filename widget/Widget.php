<?php declare(strict_types = 0);

namespace Modules\HostGroupTreeWidget;

use Zabbix\Core\CWidget;

/**
 * Host group tree widget: the tree of the Host group tree module as a dashboard navigation widget.
 * The selected host group is broadcast (_hostgroupid, _hostgroupids) to the widgets that use this widget as source.
 */
class Widget extends CWidget {

	public function getDefaultName(): string {
		return _('Host group tree');
	}
}
