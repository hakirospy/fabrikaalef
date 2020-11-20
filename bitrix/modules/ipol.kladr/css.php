<style>  
	.hinta{/*подсказочная ссылка*/
		color: #2675d7;
		border: none;
		font-size: 13px;
		margin: 0;
		border-spacing: 0;
		display: block;
		cursor:pointer;
	}
	
	.hintdiv{
		display:none;
	} 

	/*подсказки*/
	.PropHint { 
		background: url("/bitrix/images/ipol.kladr/hint.gif") no-repeat transparent;
		display: inline-block;
		height: 12px;
		position: relative;
		width: 12px;
	}
	.b-popup { 
		background-color: #FEFEFE;
		border: 1px solid #9A9B9B;
		box-shadow: 0px 0px 10px #B9B9B9;
		display: none;
		font-size: 12px;
		padding: 19px 13px 15px;
		position: absolute;
		top: 38px;
		width: 300px;
		z-index: 12;
	}
	.b-popup .pop-text { 
		margin-bottom: 10px;
		color:#000;
	}
	.pop-text i {color:#AC12B1;}
	.b-popup .close { 
		background: url("/bitrix/images/ipol.kladr/popup_close.gif") no-repeat transparent;
		cursor: pointer;
		height: 10px;
		position: absolute;
		right: 4px;
		top: 4px;
		width: 10px;
	}


/* 	.towntable tr{
		text-align:center;
	}

	.stateicon{
		width:20px;
		height:26px;
		background: url('/bitrix/images/ipol.kladr/stateicons.png');
		
	}

	.ver{
		background-position:0 0px;
	}
	.notver{
		background-position:0 -60px;
	}
	.del{
		background-position:0 -91px;
	}
	.new{
		background-position:0 -29px;
	}

	.deltr{
		background: #FFA6A6;
		border-radius:3px;
		-moz-border-radius: 3px;
		-webkit-border-radius: 3px;
		border:1px solid #FFA6A6;
	}
 */
	table{
		border-collapse:collapse;
	}
	
	.help{
		font-size:15px;
	}
	.help a,.help b{
		font-size:16px;
	}
	
	.help .hintdiv a{
		font-size:14px;
		
	}
</style>