<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/25
 * Time: 10:59
 */

function distributorArray($user_data = []){
    $result = [];
    foreach ($user_data as $item){
        $result[$item['push_id']][] = $item;
    }
    return $result;
}