<?php

namespace Kartavik\Yii2\Repository;

use MyCLabs\Enum\Enum;

/**
 * Class Sort.
 * @package Kartavik\Yii2\Repository
 *
 * @method static Sort DESC()
 * @method static Sort ASC()
 */
class Sort extends Enum
{
    public const DESC = \SORT_DESC;
    public const ASC = \SORT_ASC;
}
