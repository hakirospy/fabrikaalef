<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main\Localization\Loc;
use Yandex\Market;

if (empty($arResult['BASKET']['ITEMS'])) { return; }

$columnsCount = count($arResult['BASKET']['COLUMNS']) + 1;

?>
<h2><?= Loc::getMessage('YANDEX_MARKET_T_TRADING_ORDER_VIEW_BASKET_TITLE'); ?></h2>
<div class="adm-s-order-table-ddi">
	<table class="yamarket-basket-table adm-s-order-table-ddi-table adm-s-bus-ordertable-option" style="width: 100%;">
		<thead>
			<tr>
				<td class="tal"><?= Loc::getMessage('YANDEX_MARKET_T_TRADING_ORDER_VIEW_BASKET_ITEM_INDEX'); ?></td>
				<?php
				foreach ($arResult['BASKET']['COLUMNS'] as $columnTitle)
				{
					?>
					<td class="tal"><?= $columnTitle; ?></td>
					<?
				}
				?>
			</tr>
		</thead>
		<tbody>
			<tr></tr><?php // hack for bitrix css ?>
			<?php
			foreach ($arResult['BASKET']['ITEMS'] as $item)
			{
				?>
				<tr class="bdb-line js-yamarket-basket-item" data-plugin="OrderView.BasketItem" data-id="<?= $item['ID']; ?>">
					<td class="tal"><?= $item['INDEX']; ?></td>
					<?php
					foreach ($arResult['BASKET']['COLUMNS'] as $column => $columnTitle)
					{
						$columnValue = isset($item[$column]) ? $item[$column] : null;
						$columnFormattedKey = $column . '_FORMATTED';

						if (isset($item[$columnFormattedKey]))
						{
							$columnFormatted = $item[$columnFormattedKey];
						}
						else if ($columnValue !== null)
						{
							$columnFormatted = $columnValue;
						}
						else
						{
							$columnFormatted = '&mdash;';
						}

						switch ($column)
						{
							case 'COUNT':
							case 'BOX_COUNT':
								?>
								<td class="tal for--<?= strtolower($column); ?>">
									<?= (float)$columnValue; ?>
									<?= Loc::getMessage('YANDEX_MARKET_T_TRADING_ORDER_VIEW_BASKET_ITEM_MEASURE'); ?>
								</td>
								<?php
							break;

							case 'SUBSIDY':
								$hasPromos = !empty($item['PROMOS']);

								?>
								<td class="tal for--<?= strtolower($column); ?>">
									<?php
									if ($columnValue !== null || !$hasPromos)
									{
										echo $columnFormatted;
									}

									if ($hasPromos)
									{
										foreach ($item['PROMOS'] as $promo)
										{
											echo sprintf('<div>%s</div>', $promo);
										}
									}
									?>
								</td>
								<?php
							break;

							default:
								?>
								<td class="tal for--<?= strtolower($column); ?>"><?= $columnFormatted; ?></td>
								<?php
							break;
						}
					}
					?>
				</tr>
				<?php
			}
			?>
		</tbody>
		<?php
		if (!empty($arResult['BASKET']['SUMMARY']))
		{
			?>
			<tfoot>
				<tr>
					<td class="yamarket-basket-summary" colspan="<?= $columnsCount; ?>">
						<?php
						$isFirstSummaryItem = true;

						foreach ($arResult['BASKET']['SUMMARY'] as $summaryItem)
						{
							echo $isFirstSummaryItem ? '' : '<br />';
							echo $summaryItem['NAME'] . ': ' . $summaryItem['VALUE'];

							$isFirstSummaryItem = false;
						}
						?>
					</td>
				</tr>
			</tfoot>
			<?php
		}
		?>
	</table>
</div>