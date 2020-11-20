<?php

namespace Ipolh\SDEK;


class abstractGeneral
{
    protected static $MODULE_LBL = IPOLH_SDEK_LBL;
    protected static $MODULE_ID  = IPOLH_SDEK;

    /**
     * @return string
     */
    public static function getMODULELBL()
    {
        return self::$MODULE_LBL;
    }

    /**
     * @return string
     */
    public static function getMODULEID()
    {
        return self::$MODULE_ID;
    }
}