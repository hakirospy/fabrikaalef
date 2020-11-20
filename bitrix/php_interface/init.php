<?
AddEventHandler("main", "OnEndBufferContent", "ChangeMyContent");

function ChangeMyContent()
{
   global $USER,$APPLICATION;
  if (CModule::IncludeModule("sale"))
    {
		if(CCatalogDiscountCoupon::IsExistCoupon('EARLYNOVEMBER15')){
			CCatalogDiscountCoupon::SetCoupon('EARLYNOVEMBER15');}}

}?>