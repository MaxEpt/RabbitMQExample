<?php

namespace RabbitMQExample;

use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractProducer implements IProducer
{
    protected $dataToProduce;

    protected $rabbitConnection;

    /**
     * AbstractProducer constructor.
     * @param array $connectionParams
     *  $connectionData => [
     *    'host'     => RabbitMQ host,
     *    'port'     => RabbitMQ port,
     *    'user'     => User name,
     *    'password' => Password,
     *    'vhost'    => Vhost to connect(default is /)
     * ]
     * @param array $dataToProduce Data to send
     */
    public function __construct(array $connectionParams, array $dataToProduce)
    {
        $this->rabbitConnection = new RabbitConnection($connectionParams);
        $this->dataToProduce = $dataToProduce;
    }

    public function produce(): bool
    {
        try {
            $this->rabbitConnection->openConnection();

            $this->rabbitConnection
                ->getChannel()
                ->exchange_declare($this->getExchangeName(), 'direct', false, true, false);

            $this->publish();

            $this->rabbitConnection->closeConnection();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function publish()
    {
        $data['start'] = time();
        $data['data'] = $this->dataToProduce;

        $msg = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->rabbitConnection
            ->getChannel()
            ->basic_publish($msg, $this->getExchangeName());
    }

    abstract protected function getExchangeName(): string;
}
