<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$this->setFrameMode(true);

$frame = $this->createFrame()->begin('');
	if(CModule::IncludeModule('arturgolubev.ecommerce')):
		$finalScripts = CArturgolubevEcommerce::checkReadyEvents();
		if($finalScripts):
		?>
			<script>
				if (window.frameCacheVars !== undefined) 
				{
					// console.log('=> frameCacheVars finalScripts');
					<?=$finalScripts?>
				}
				else
				{
					document.addEventListener("DOMContentLoaded", function(){
						// console.log('=> DOMContentLoaded finalScripts');
						<?=$finalScripts?>
					});
				}
			</script>
			<?
		endif;
	endif;
$frame->end();
?>