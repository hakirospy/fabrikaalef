<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<div class="social-icons">
	<?if($arParams["SOCIAL_TITLE"] && (!empty($arResult["SOCIAL_VK"]) || !empty($arResult["SOCIAL_ODNOKLASSNIKI"]) || !empty($arResult["SOCIAL_FACEBOOK"]) || !empty($arResult["SOCIAL_TWITTER"]) || !empty($arResult["SOCIAL_INSTAGRAM"]) || !empty($arResult["SOCIAL_MAIL"]) || !empty($arResult["SOCIAL_YOUTUBE"]) || !empty($arResult["SOCIAL_GOOGLEPLUS"]))):?>
		<div class="small_title"><?=$arParams["SOCIAL_TITLE"];?></div>
	<?endif;?>
	<!-- noindex -->
	<ul>
		<?if(!empty($arResult['SOCIAL_VK'])):?>
			<li class="vk">
				<a href="<?=$arResult['SOCIAL_VK']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_VK')?>">
					<?=CMax::showIconSvg("vk", SITE_TEMPLATE_PATH.'/images/svg/social/social_vk.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_FACEBOOK'])):?>
			<li class="facebook">
				<a href="<?=$arResult['SOCIAL_FACEBOOK']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_FACEBOOK')?>">
					<?=CMax::showIconSvg("fb", SITE_TEMPLATE_PATH.'/images/svg/social/Facebook.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_ODNOKLASSNIKI'])):?>
			<li class="odn">
				<a href="<?=$arResult['SOCIAL_ODNOKLASSNIKI']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_ODNOKLASSNIKI')?>">
					<?=CMax::showIconSvg("odn", SITE_TEMPLATE_PATH.'/images/svg/social/Odnoklassniki.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_TWITTER'])):?>
			<li class="twitter">
				<a href="<?=$arResult['SOCIAL_TWITTER']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_TWITTER')?>">
					<?=CMax::showIconSvg("tw", SITE_TEMPLATE_PATH.'/images/svg/social/social_twitter.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_INSTAGRAM'])):?>
			<li class="instagram">
				<a href="<?=$arResult['SOCIAL_INSTAGRAM']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_INSTAGRAM')?>">
					<?=CMax::showIconSvg("inst", SITE_TEMPLATE_PATH.'/images/svg/social/Instagram.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_YOUTUBE'])):?>
			<li class="ytb">
				<a href="<?=$arResult['SOCIAL_YOUTUBE']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_YOUTUBE')?>">
					<?=CMax::showIconSvg("ytb", SITE_TEMPLATE_PATH.'/images/svg/social/Youtube.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_GOOGLEPLUS'])):?>
			<li class="gplus">
				<a href="<?=$arResult['SOCIAL_GOOGLEPLUS']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_GOOGLEPLUS')?>">
					<?=CMax::showIconSvg("gplus", SITE_TEMPLATE_PATH.'/images/svg/social/Googleplus.svg');?>
				</a>
			</li>
		<?endif;?>
		<?if(!empty($arResult['SOCIAL_MAIL'])):?>
			<li class="mail">
				<a href="<?=$arResult['SOCIAL_MAIL']?>" target="_blank" rel="nofollow" title="<?=GetMessage('TEMPL_SOCIAL_MAILRU')?>">
					<?=CMax::showIconSvg("mail", SITE_TEMPLATE_PATH.'/images/svg/social/Mailru.svg');?>
				</a>
			</li>
		<?endif;?>
	</ul>
	<!-- /noindex -->
</div>