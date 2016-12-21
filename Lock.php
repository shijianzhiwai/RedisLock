<?php

/**
* 
*/
class RedisLock
{
	//redis连接
	private $redis = null;

	//redis地址
	private $redis_host = '127.0.0.1';

	//redis端口
	private $redis_port = 6379;

	//redis密码
	private $redispass = '123456';

	//redis选择库
	private $redis_select = 1;

	function __construct()
	{
		$redis = new Redis();
        $redis->connect($this->redis_host, $this->redis_port);
        if (strlen($this->redispass) !== 0)
        {
        	$redis->auth($this->redispass);	
        }
        $redis->select($this->redis_select);
        $this->redis = $redis;
	}

	/*FROM: http://redis.io/topics/distlock
     *设置锁函数 ttl(ms) 可以在非model层调用
     */
    public function redisLock($resource, $ttl=3000)
    {
        $token = uniqid();//产生唯一时间token
        
        if ($this->lockInstance($resource, $token, $ttl)) 
        {
            return array(
                'resource' => $resource,
                'token'    => $token,
            );
        }
        return false;
    }

    //解锁函数 需要使用加锁时返回的token才能解锁
    public function redisUnlock($resource,$token)
    {
        return $this->unlockInstance($resource, $token);
    }

    //设置内部锁函数
    private function lockInstance($resource, $token, $ttl)
    {
        $redis  = $this->redis;
        $result = $redis->setNx($resource,$token);
        $result AND $redis->pexpire($resource, $ttl);
        return $result;
        //Need Redis >= 2.6.12
        //return $redis->set($resource, $token, array('NX', 'PX' => $ttl));
    }

    private function unlockInstance($resource, $token)
    {   
        $redis = $this->redis;
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $redis->eval($script, array($resource, $token), 1);
    }

    //不必验证token解锁
    public function redisUnlockNt($resource){
        $redis = $this->redis;
        //直接删除
        $redis->del($resource);
    }
}