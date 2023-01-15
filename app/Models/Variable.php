<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

class Variable extends BaseModel
{

    /** @var string */
    protected $table = 'sendportal_variable';

    /** @var array */
    protected $fillable = [
        'name',
        'description',
        'value_type',
        'value_from'
    ];

    static public $value_types_map = [
        1 => '随机数值',
        2 => '随机可逆字符串',
        3 => '随机可逆字符串带时间限制',
        4 => '固定值',
        5 => 'Web hook'
    ];

    protected function getValueTypeNameAttribute()
    {
        return Variable::$value_types_map[$this->value_type];
    }
}
