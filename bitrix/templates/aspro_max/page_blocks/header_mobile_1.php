<?
global $arTheme, $arRegion;
$logoClass = ($arTheme['COLORED_LOGO']['VALUE'] !== 'Y' ? '' : ' colored');
?><div class="mobileheader-v2">
	<div class="burger pull-left">
		<?=CMax::showIconSvg("burger dark", SITE_TEMPLATE_PATH."/images/svg/burger.svg");?>
		<?=CMax::showIconSvg("close dark", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
	</div>
	<div class="logo-block pull-left">
		<div class="logo<?=$logoClass?>">

<a href="/"><img src="/upload/CMax/7ce/FA-logo-white2.png" alt="Фабрика ' alef' верхняя одежда от производителя" title="Фабрика " data-src=""></a>
			<?//CMax::ShowLogo();?>
		</div>
	</div>
	<div class="right-icons pull-right">
		<div class="pull-right">
			<div class="wrap_icon wrap_basket">
				<?=CMax::ShowBasketWithCompareLink('', 'big', false, false, true);?>
			</div>
		</div>
		<div class="pull-right">
			<div class="wrap_icon wrap_cabinet">
				<?=CMax::showCabinetLink(true, false, 'big');?>
			</div>
		</div>
		<div class="pull-right">
			<div class="wrap_icon">
				<button class="top-btn inline-search-show twosmallfont">
					<?=CMax::showIconSvg("search", SITE_TEMPLATE_PATH."/images/svg/Search.svg");?>
				</button>
			</div>
		</div>
		<div class="pull-right">
			<div class="wrap_icon wrap_phones">
				<?CMax::ShowHeaderMobilePhones("big");?>
			</div>
		</div>
	</div>
</div>