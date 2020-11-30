<?php

namespace app\test\model;

use think\Model;

class Cart extends Model
{
    protected $resultSetType='collection';
    function cartUser(){
        return $this->belongsTo('User',"user_id");
    }
    function cartGoods(){
        return $this->belongsTo('Goods',"goods_id");
    }

}
