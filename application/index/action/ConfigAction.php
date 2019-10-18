<?php

namespace app\index\action;

use think\Db;

class ConfigAction{

	public function schoolConfig(){
		$school = [
			1 => '江南大学',
			2 => '长江学院',
		];
        return $school;
	}

}

