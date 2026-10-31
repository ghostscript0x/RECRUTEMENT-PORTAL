<?php

namespace Tests\Feature;

use Tests\TestCase;

class RedisQueueConfigurationTest extends TestCase
{
    public function test_redis_queue_connection_is_configured_to_dispatch_after_database_commit(): void
    {
        $redisQueue = config('queue.connections.redis');

        $this->assertSame('redis', $redisQueue['driver']);
        $this->assertSame('emails', $redisQueue['queue']);
        $this->assertTrue($redisQueue['after_commit']);
    }
}
