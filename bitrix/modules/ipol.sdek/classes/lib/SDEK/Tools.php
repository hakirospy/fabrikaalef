<?php

namespace Ipolh\SDEK\SDEK;


class Tools
{
    public static function getTrackLink($trackNumber='')
    {
        return 'http://www.cdek.ru/track.html?order_id='.$trackNumber;
    }
}