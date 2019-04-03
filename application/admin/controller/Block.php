<?php
/**
 */
namespace app\admin\controller;
use app\admin\logic\GoodsLogic;
use app\common\logic\GoodsActivityLogic;
use app\common\logic\ActivityLogic;

use think\Db;

class Block extends Base{


	//删除页面
	public function delete(){
		$id=I('post.id');
		if($id){
			$r = D('mobile_template')->where('id', $id)->delete();
    		exit(json_encode(1));
		}
	}

}
?>