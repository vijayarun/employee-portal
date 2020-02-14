<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

require_once __DIR__ . '/SingletonTrait.php';
require_once __DIR__ . './../vendor/autoload.php';

/**
 * This class used to handle the Queue functionality.
 *
 * Class RabbitMQHelper
 *
 * @author A Vijay<mailvijay.vj@gmail.com>
 */
class RabbitMQHelper
{
    use SingletonTrait;

    public const QUEUE_IMPORT = 'import';

    /**
     * @var AMQPStreamConnection
     */
    private AMQPStreamConnection $connection;
    /**
     * @var AMQPChannel
     */
    private AMQPChannel $channel;

    /**
     * RabbitMQHelper constructor.
     */
    public function __construct()
    {
        $config = (require __DIR__ . './../config.php')['queue'];

        $this->connection = new AMQPStreamConnection($config['host'], $config['port'], $config['username'], $config['password']);
        $this->channel = $this->connection->channel();
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
//        $this->channel->close();
//        $this->connection->close();
    }

    /**
     * @param $queue
     * @param $msg
     */
    public function publish($queue, $msg): void
    {
        $this->channel->queue_declare($queue, false, false, false, false);

        if (is_array($msg)) {
            $msg = json_encode($msg, JSON_THROW_ON_ERROR, 512);
        }

        $this->channel->basic_publish(new AMQPMessage($msg), '', $queue);
    }

    /**
     * @param $queue
     * @param $callback
     * @throws ErrorException
     */
    public function consume($queue, $callback): void
    {
        $this->channel->queue_declare($queue, false, false, false, false);
        $this->channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    /**
     * @param AMQPMessage $msg
     */
    public function ack(AMQPMessage $msg): void
    {
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }
}