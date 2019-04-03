<?php
/**
 */

namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\common\model\Order;
use app\common\model\TeamActivity;
use app\common\model\TeamFollow;
use app\common\model\TeamFound;
use think\Loader;
use think\Db;
use think\Page;

class Team extends Base
{
	public function index()
	{
	header("Content-type: text/html; charset=utf-8");

	}
}
