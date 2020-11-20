<?php

namespace Yandex\Market\Trading\Service\Marketplace;

use Yandex\Market;
use Bitrix\Main;
use Yandex\Market\Trading\Service as TradingService;

class Provider extends TradingService\Common\Provider
{
	protected $paySystem;
	protected $promo;

	protected function createRouter()
	{
		return new Router($this);
	}

	protected function createInstaller()
	{
		return new Installer($this);
	}

	protected function createOptions()
	{
		return new Options($this);
	}

	protected function createInfo()
	{
		return new Info($this);
	}

	protected function createStatus()
	{
		return new Status($this);
	}

	protected function createPrinter()
	{
		return new Printer($this);
	}

	protected function createModelFactory()
	{
		return new ModelFactory($this);
	}

	public function getPaySystem()
	{
		if ($this->paySystem === null)
		{
			$this->paySystem = $this->createPaySystem();
		}

		return $this->paySystem;
	}

	protected function createPaySystem()
	{
		return new PaySystem($this);
	}

	public function getPromo()
	{
		if ($this->promo === null)
		{
			$this->promo = $this->createPromo();
		}

		return $this->promo;
	}

	protected function createPromo()
	{
		return new Promo($this);
	}
}