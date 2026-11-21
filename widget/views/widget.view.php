<?php declare(strict_types = 0);

/**
 * Host group tree widget view: the data is sent as JSON variables, the tree is built by class.widget.js.
 *
 * @var CView $this
 * @var array $data
 */

$view = new CWidgetView($data);

foreach ($data['vars'] as $name => $value) {
	$view->setVar($name, $value);
}

$view->show();
