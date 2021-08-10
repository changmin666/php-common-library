<?php
declare (strict_types=1);

namespace PXCommon\IDGen;

use Exception;
use Redis;

class IDGen
{
    //time 30bit +  dc 4bits +  business 2bits  +  seq 16bits
    const START_TIMESTAMP = 1608016361;                                 // 起始时间戳

    const DC_BITS = 4;                                                  // 数据中心标识位数
    const BUSINESS_BITS = 2;                                            // 业务标识位
    const SEQ_BITS = 16;                                                // 秒内自增位

    private int $dataCenterID = 0;                                          // 数据中心ID
    private int $businessID = 0;                                            // 业务ID

    protected int $maxDataCenterID = -1 ^ (-1 << self::DC_BITS);                          // 数据中心最大数
    protected int $maxBusinessID = -1 ^ (-1 << self::BUSINESS_BITS);                      // 业务最大数

    protected int $timestampLeftShift = self::DC_BITS + self::BUSINESS_BITS + self::SEQ_BITS;                   // 时间戳左偏移位数
    protected int $dataCenterLeftShift = self::BUSINESS_BITS + self::SEQ_BITS;                                  // 数据中心ID偏左移位数
    protected int $businessLeftShift = self::SEQ_BITS;                                                          // 业务ID左偏移位数
    protected int $seqMask = -1 ^ (-1 << self::SEQ_BITS); //序列号掩码 65535

    private Redis $redis;
    protected string $redisKeyPrefix = 'px:id:gen:';
    protected int $redisKeyExpireTime = 60 * 5;

    /**
     * 构造ID生成器对象
     * PxIDGen constructor.
     * @param Redis $redisObj
     * @param int $dataCenterID
     * @param int $businessID
     * @throws Exception
     */
    public function __construct(Redis $redisObj, int $dataCenterID, int $businessID)
    {
        if ($dataCenterID > $this->maxDataCenterID || $dataCenterID < 0) {
            throw new Exception("data center id can't be greater than {$this->maxDataCenterID} or less than 0");
        }
        if ($businessID > $this->maxBusinessID || $businessID < 0) {
            throw new Exception("business can't be greater than {$this->maxBusinessID} or less than 0");
        }
        $this->dataCenterID = $dataCenterID;
        $this->businessID = $businessID;
        $this->redis = $redisObj;
    }

    /**
     * 生成ID
     * @return int
     * @throws Exception
     */
    public function nextID(): int
    {
        $timestamp = $this->timeGen();
        $redisKey = $this->redisKeyPrefix . $this->dataCenterID . ':' . $this->businessID . ':' . $timestamp;
        $seq = $this->redis->incr($redisKey);
        // 设置seq key为5分钟过期，允许时钟回拨的最大系数为5分钟
        // 时钟回拨后如果发号器使用不到65535则继续从最后的seqId开始自增
        // 但是如果5分钟前发号器已经使用了65535个序列号那么将发号失败
        // 时钟如果回拨ID的自增性就会消失
        $this->redis->expire($redisKey, $this->redisKeyExpireTime);

        if (!$seq || !($seq & $this->seqMask)) {
            throw new Exception('gen seq was fail');
        }

        return (bcsub((string)$timestamp, (string)self::START_TIMESTAMP) << $this->timestampLeftShift) |
            ($this->dataCenterID << $this->dataCenterLeftShift) |
            ($this->businessID << $this->businessLeftShift) |
            $seq;
    }

    /**
     * 获取当前秒级时间戳
     * @return int
     */
    private function timeGen(): int
    {
        return time();
    }

}