<?php declare(strict_types = 0);

namespace Modules\HostGroupTree;

use Zabbix\Core\CModule;

/**
 * Host group tree for Monitoring > Hosts.
 *
 * The module does not replace the native host.view action: the tree is injected client-side by
 * assets/js/hostgrouptree.js, so the native page (filter tabs, list, table customizations, refresh)
 * stays untouched and keeps working across minor upgrades.
 */
class Module extends CModule {
}
