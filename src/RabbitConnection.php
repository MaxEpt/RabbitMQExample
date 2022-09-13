<?php

namespace RabbitMQExample;

use PhpAmqpLib\Connection\AMQPStreamConnection;

final class RabbitConnection
{
    private $connection;

    private $channel;

    private $connectionParams;

    private $connectionOptions = ['heartbeat' => Constants::DEFAULT_HEARTBEAT, 'keepalive' => true];

    /**
     * RabbitConnection constructor.
     * @param array $connectionParams
     * @param array $connectionOptions
     */
    public function __construct(array $connectionParams, array $connectionOptions = [])
    {
        if (!empty($options)) {
            $this->connectionOptions = array_merge($this->connectionOptions, $connectionOptions);
        }

        $this->connectionParams = $connectionParams;
    }

    public function openConnection()
    {
        $this->connection = AMQPStreamConnection::create_connection(
            $this->connectionParams,
            $this->connectionOptions
        );

        $this->channel = $this->connection->channel();
    }

    public function closeConnection()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function getChannel()
    {
        return $this->channel;
    }
}
