<?php
	IncludeModuleLangFile(__FILE__);
?>
<div class="sberbank_pokupay__resize" >
	<div class="sberbank_pokupay2__wrapper">
		<div class="sberbank_pokupay2__inner">
			<div class="sberbank_pokupay2__content">
				<div class="sberbank_pokupay2__row">
					<div class="sberbank_pokupay2__col _left">
						<?php if (in_array($params['sberbank_result']['errorCode'], array(999, 1, 2, 3, 4, 5, 7, 8))) { ?>
							<span class="sberbank_pokupay__error-code"><?=getMessage("SBERBANK_POKUPAY_ERROR_TITLE");?><?=$params['sberbank_result']['errorCode']?></span>
							<span class="sberbank_pokupay__error-message"><?=$params['sberbank_result']['errorMessage']?></span>

						<?php } else { ?>
							<span class="sberbank_pokupay2__summ" style="margin-bottom: 0">
								<?= getMessage("SBERBANK_POKUPAY_ALERT"); ?>
							</span>

							<span class="sberbank_pokupay2__summ _hidden">
								<?= getMessage("SBERBANK_POKUPAY_TITLE"); ?>: <br class="mobile_br">
								<span style="white-space: nowrap;">
									<b class="sberbank_pokupay2__summ-number"><?= number_format($params['ORDER_AMOUNT'], 2, '.', ' '); ?> </b>
									<img class="sberbank_pokupay2__ruble-img" src="/bitrix/images/sberbank.pokupay/ruble-2.svg" alt="">
								</span>
							</span>

							<a href="<?=$params['payment_link']?>" class="sberbank_pokupay2__submit _hidden _credit-type-<?=$params['CREDIT_TYPE'];?>">
								<?php if( $params['CREDIT_TYPE'] == 2 ) { ?>
									<span><?=getMessage("SBERBANK_POKUPAY_NAME_TYPE_2");?></span>
								<?php } else { ?>
									<?=getMessage("SBERBANK_POKUPAY_NAME_TYPE_1");?>
								<?php }?>
							</a>
							<?php if( $params['CREDIT_TYPE'] == 'CREDIT' ) { ?>
								<span class="sberbank_pokupay2__payment-min-pay _hidden">
									<?= getMessage("SBERBANK_POKUPAY_MIN_PAY_1");?>
									<?php echo number_format(round($params['ORDER_AMOUNT'] / 27.66,2), 2, '.', ' '); ?> 
									<img class="sberbank_pokupay2__ruble-img" src="/bitrix/images/sberbank.pokupay/ruble-2.svg" alt="">
									<?= getMessage("SBERBANK_POKUPAY_MIN_PAY_2");?>
								</span>
							<?php } ?>
							<?php if( $params['CREDIT_TYPE'] == 'INSTALLMENT' ) { ?>
								<span class="sberbank_pokupay2__payment-min-pay _hidden">
									<?= getMessage("SBERBANK_POKUPAY_MIN_PAY_1");?>
									<?php echo number_format(round($params['ORDER_AMOUNT'] / $params['CREDIT_MAX_MONTH'],2), 2, '.', ' '); ?> 
									<img class="sberbank_pokupay2__ruble-img" src="/bitrix/images/sberbank.pokupay/ruble-2.svg" alt="">
									<?= getMessage("SBERBANK_POKUPAY_MIN_PAY_2");?>
								</span>
							<?php } ?>
							<?php if(!isset($_GET['debug'])) {?>
								<script>
									 setTimeout(function() {
										 window.location = '<?=$params['payment_link']?>';
									 },3000)
								</script>
							<?php }?>
						<?php }?>
					</div>
					<div class="sberbank_pokupay2__col _right">
						<img class="sberbank_pokupay2__bank-logo" src="/bitrix/images/sberbank.pokupay/bank_logo.svg" alt="">
					</div>
				</div>
			</div>
<!-- 			<div class="sberbank_pokupay2__footer">
				<span class="sberbank_pokupay2__footer-text">
					<?= getMessage("SBERBANK_POKUPAY_DESCRIPTION_TEXT");?>
					<a class="sberbank_pokupay2__descr-link" target="_blank" href="https://www.pokupay.ru/credit_terms"><?= getMessage("SBERBANK_POKUPAY_DESCRIPTION_LINK");?></a>.
				</span>
			</div> -->
		</div>

	</div>	
</div>




<style>
	.col img {
/*		height: auto !important;
		width: auto !important;	*/
	}
	._hidden {
		display: none !important;
	}
	body .sberbank_pokupay2__wrapper * {
		box-sizing: border-box;
	}
	body .sberbank_pokupay__error-code {
		font-family: arial;
		color: red;
		font-size: 20px;
		display: block;
		margin-top:5px;
		margin-bottom: 7px;
	}
	body .sberbank_pokupay__error-message {
		font-family: arial;
		color:#000;
		font-size: 14px;
		display: block;
	}
	body .sberbank_pokupay2__wrapper {
		max-width: 530px;
		width: 100%;
		background: #fff;
		border:1px solid #ddd;
		border-radius: 4px;
		margin: 5px 0 20px;
	}
	body .sberbank_pokupay2__inner {}
	body .sberbank_pokupay2__row {
		display: flex;
		flex-wrap: nowrap;
		flex-direction: row;
	}
	body .sberbank_pokupay2__col {}
	body .sberbank_pokupay2__col._left {
		padding-right: 20px;
		width: 290px;
	}
	body .sberbank_pokupay2__col._right {
		margin-left: auto;
		width: auto;
	}
	body .sberbank_pokupay2__content {
		padding: 20px 20px 15px;
	}
	body .sberbank_pokupay2__footer {
		background: #ddd;
		padding: 10px 20px;
		margin: 0;
	}
	body .sberbank_pokupay2__footer-text {
		font-size: 12px;
		color: #000;
		line-height: 17px;
		display: block;
		font-family: arial;
	}
	body .sberbank_pokupay2__descr-link {
		font-size:12px;
		color: #000;
		text-decoration: underline;
		line-height: 17px;
		font-family: arial;
	}
	body .sberbank_pokupay2__descr-link:hover {
		text-decoration: none;
	}
	body .sberbank_pokupay2__summ {
		font-size: 20px;
		line-height: 24px;
		display: block;
		margin:0px 0 22px;
		font-family: arial;
	}
	.sberbank_pokupay2__summ-number {
		font-size: 22px;
		line-height: 24px;
		font-weight: bold;
		display:inline-block !important;
		font-family: arial;
	}
	body .sberbank_pokupay2__ruble-img {
	    width: 20px;
	    height: 17px;
	    display: inline-block;
	    vertical-align: top;
	    margin-top: 2px;
	}
	body .sberbank_pokupay2__submit {
		font-family: arial;
		font-size: 14px;
		line-height: 17px;
		background: url(/bitrix/images/sberbank.pokupay/sber_logo_icon.svg) no-repeat 2px center;
		background-size: 44px;
		background-color: #00be64;
		color: #fff;
		padding: 0px 30px 0px 55px;
		margin: 0 0 4px 0;
		text-decoration: none !important;
		outline: none;
	    min-height: 44px;
	    display: flex;
	    border:none;
	    outline: none;
	    align-items: center;
	    min-width: 170px;
	    max-width: 300px;
	    width: auto;
	    font-size: 20px;
	    line-height: 22px;
	}
	body .sberbank_pokupay2__submit._credit-type-2 {
		padding-top: 2px;
		font-size: 18px;
		line-height: 15px;
	}
	body .sberbank_pokupay2__submit._credit-type-2 em {
		font-size: 12px;
		line-height: 15px;
		font-style: normal;
	}
	body .sberbank_pokupay2__submit:hover {
		background-color: #079d57;
		color: #fff;
		text-decoration: none !important;
	}
	body .sberbank_pokupay2__payment-min-pay {
		display: block;
		font-family: arial;
		font-size: 14px;
		line-height: 16px;
		font-weight: bold;
		color: #000;
		margin: 8px 0 0;
		padding:  0 0 0;
		text-align: left;
	}
	body .sberbank_pokupay2__payment-min-pay .sberbank_pokupay2__ruble-img {
		height: 12px;
		width: 12px;
		margin: 2px 0 0 0;
	}
	body .sberbank_pokupay2__bank-logo {
		max-width: 160px;
		width: 100%;
	}

	/* MOBILE */
	body ._mobile .sberbank_pokupay2__row {
		flex-direction: column;
	}	
	body ._mobile .sberbank_pokupay2__col._left {
		order:2;
		width: 100%;
		padding: 0;
	}	
	body ._mobile .sberbank_pokupay2__col._right {
		order:1;
		margin: 0 0 20px;
		padding: 0;
		width: 100%;
	}	
	body ._mobile .sberbank_pokupay2__wrapper {
		width: 100%;
		max-width: 340px;
	}
	body ._mobile .sberbank_pokupay2__submit {
		width: 100%;
		max-width: 100%;
	}



	body table {
		width: 100%;
	}
	body .ps_logo {
		display: none;
	}
	.mobile_br {display: none;}


</style>
<script>
	var sberbank_wrap = document.getElementsByClassName('sberbank_pokupay__resize')[0];
	function changeViewMode(e) {
		var wrap_width = sberbank_wrap.offsetWidth;
		if(wrap_width < 700) {
			sberbank_wrap.classList.add("_mobile");
		} else {
			sberbank_wrap.classList.remove("_mobile");
		}
	}
	changeViewMode();
	window.onresize = changeViewMode;
</script>