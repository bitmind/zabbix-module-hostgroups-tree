<?php
// Empty Zabbix classes: enough to load the module classes and call their static helpers via reflection.
namespace {
	class CController { protected function disableCsrfValidation() {} }
	class CControllerDashboardWidgetView {}
	class API {}
	class CControllerResponseData {}
	class CRoleHelper {}
	class CSeverityHelper {}
	class CWebUser {}

	$GLOBALS['failures'] = 0;

	function check(string $label, $actual, $expected): void {
		if ($actual === $expected) {
			echo "ok   $label\n";
		}
		else {
			echo "FAIL $label\n  expected: ".json_encode($expected)."\n  actual:   ".json_encode($actual)."\n";
			$GLOBALS['failures']++;
		}
	}

	function done(): void {
		exit($GLOBALS['failures'] ? 1 : 0);
	}
}

namespace Modules\HostGroupTreeWidget\Includes {
	class WidgetForm {
		public const PROBLEMS_ALL = 0;
		public const PROBLEMS_UNACKNOWLEDGED = 1;
		public const PROBLEMS_NONE = 2;
	}
}
