<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style as XlsxStyle;

class Style
{
    /**
     * 紅色
     *
     * @param XlsxStyle $style
     */
    public static function setStyleRed(XlsxStyle $style)
    {
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("FFAFAF");
        $style->getFont()->getColor()->setRGB("C00000");
    }

    /**
     * 綠色
     *
     * @param XlsxStyle $style
     */
    public static function setStyleGreen(XlsxStyle $style)
    {
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("CCE4BE");
        $style->getFont()->getColor()->setRGB("375623");
    }

    /**
     * 深紅色
     *
     * @param XlsxStyle $style
     */
    public static function setStyleDeepRed(XlsxStyle $style)
    {
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("C00000");
        $style->getFont()->getColor()->setRGB("FFFFFF");
    }

    /**
     * 深綠色
     *
     * @param XlsxStyle $style
     */
    public static function setStyleDeepGreen(XlsxStyle $style)
    {
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB("4F6228");
        $style->getFont()->getColor()->setRGB("FFFFFF");
    }
}
