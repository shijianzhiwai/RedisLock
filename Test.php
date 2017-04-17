<?php
include 'Lock.php';

$lock = new RedisLock();

$key = 'test_key';
$info = null;

//互斥锁
$info = $lock->blockRedisLock($key)

//TODO
//...

$lock->redisUnlock($key,$info['token']);