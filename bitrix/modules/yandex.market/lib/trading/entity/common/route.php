<?php

namespace Yandex\Market\Trading\Entity\Common;

use Yandex\Market;
use Bitrix\Main;

class Route extends Market\Trading\Entity\Reference\Route
{
	public function getPublicPath($serviceCode, $siteId)
	{
		return $this->getPublicBasePath() . '/' . $serviceCode . '/' . $siteId;
	}

	public function installPublic($siteId)
	{
		$rule = $this->getUrlRewriteRule();

		Main\UrlRewriter::add($siteId, $rule);
	}

	public function uninstallPublic($siteId)
	{
		$rule = $this->getUrlRewriteRule();
		unset($rule['RULE']);

		Main\UrlRewriter::delete($siteId, $rule);
	}

	protected function getUrlRewriteRule()
	{
		$path = $this->getPublicBasePath();

		return [
			'CONDITION' => '#^' . $path . '/#',
			'RULE' => '',
			'ID' => '',
			'PATH' => $path . '/index.php',
		];
	}

	protected function getPublicBasePath()
	{
		$moduleName = Market\Config::getModuleName();

		return BX_ROOT . '/services/' . $moduleName . '/trading';
	}
}