<?php
include 'Lock.php';

$lock = new RedisLock();

$key = 'test_key';
$info = null;

while(TRUE){
	$info = $lock->redisLock($key)
	if ($info) {
		break;
	}
	//休眠随机40-60ms,提高锁命中率
    usleep(mt_rand(40,60));
}

//some code...
//保证队列执行

$lock->redisUnlock($key,$info['token']);