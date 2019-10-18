<?php
namespace app\index\model;

use think\Model;

class LogModel extends Model{
	// 设置当前模型对应的完整数据表名称
    protected $table = 'log';

    public function savelog($name='', $content = '')
    {
    	$data 	= array('name' => $name, 'content' => $content, 'addtime' => time());
		$r = db('log')->insert($data);
		return $r;
    }


}
