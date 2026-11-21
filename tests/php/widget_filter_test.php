<?php
// Widget: root / excluded host groups. Run: php tests/php/widget_filter_test.php
require __DIR__.'/stubs.php';
require __DIR__.'/../../hostgrouptree-widget/actions/WidgetView.php';

$class = new ReflectionClass('Modules\HostGroupTreeWidget\Actions\WidgetView');
$filter = $class->getMethod('filterGroups');
$drop = $class->getMethod('dropPrefixesAboveRoots');

$groups = [];

foreach (['Sites', 'Sites/Rome', 'Sites/Rome/DC1', 'Sites/Milan', 'Sites2', 'Servers/Linux', 'Servers/Linux/Web',
		'Other/X/Y'] as $i => $name) {
	$groups[$i + 1] = ['groupid' => $i + 1, 'name' => $name];
}

$names = static fn(array $list): array => array_values(array_column($list, 'name'));

check('roots Sites/Rome + Servers/Linux', $names($filter->invoke(null, $groups, [2, 6], [])),
	['Sites/Rome', 'Sites/Rome/DC1', 'Servers/Linux', 'Servers/Linux/Web']
);
check('exclude Sites/Rome (Sites2 is not a subgroup)', $names($filter->invoke(null, $groups, [], [2])),
	['Sites', 'Sites/Milan', 'Sites2', 'Servers/Linux', 'Servers/Linux/Web', 'Other/X/Y']
);
check('root Sites, exclude Sites/Milan', $names($filter->invoke(null, $groups, [1], [4])),
	['Sites', 'Sites/Rome', 'Sites/Rome/DC1']
);
check('unreadable root = empty tree', $filter->invoke(null, $groups, [99], []), []);

// Node map as built by getTree().
$build = static function (array $list): array {
	$nodes = [];

	foreach ($list as $group) {
		$path = null;

		foreach (explode('/', $group['name']) as $part) {
			$parent = $path;
			$path = $path === null ? $part : $path.'/'.$part;
			$nodes[$path] ??= ['path' => $path, 'name' => $part, 'parent' => $parent, 'groupid' => null];
		}

		$nodes[$path]['groupid'] = $group['groupid'];
	}

	return $nodes;
};

$kept = $filter->invoke(null, $groups, [2, 6, 8], []);
$nodes = $drop->invoke(null, $build($kept), $kept);

check('prefixes above roots dropped, roots with full name', array_map(
	static fn(array $node): array => [$node['name'], $node['parent']],
	$nodes
), [
	'Sites/Rome' => ['Sites/Rome', null],
	'Sites/Rome/DC1' => ['DC1', 'Sites/Rome'],
	'Servers/Linux' => ['Servers/Linux', null],
	'Servers/Linux/Web' => ['Web', 'Servers/Linux'],
	'Other/X/Y' => ['Other/X/Y', null]
]);

done();
