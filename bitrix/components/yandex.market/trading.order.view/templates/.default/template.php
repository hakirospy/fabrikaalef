<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Yandex\Market;

Market\Ui\Library::loadConditional('jquery');

Market\Ui\Assets::loadPluginCore();
Market\Ui\Assets::loadFieldsCore();

$blocks = [
	'PROPERTIES',
	'BASKET',
	'SHIPMENT',
];

foreach ($blocks as $block)
{
	if (empty($arResult[$block])) { continue; }

	include __DIR__ . '/partials/block-' . strtolower($block) . '.php';
}