<?php

namespace RabbitMQExample;

use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractConsumer implements IConsumer
{
    protected $rabbitConnection;

    private $declareExchangeStrategy;

    /**
     * AbstractConsumer constructor.
     * @param array $connectionParams
     *  $connectionData => [
     *    'host'     => RabbitMQ host,
     *    'port'     => RabbitMQ port,
     *    'user'     => User name,
     *    'password' => Password,
     *    'vhost'    => Vhost to connect(default is /)
     * ]
     * @param IDeclareExchangeStrategy|null $declareExchangeStrategy
     */
    public function __construct(array $connectionParams, IDeclareExchangeStrategy $declareExchangeStrategy = null)
    {
        $this->rabbitConnection = new RabbitConnection($connectionParams);

        if (!$declareExchangeStrategy) {
            $this->declareExchangeStrategy = new DefaultDeclareExchangeStrategy();
        }
    }

    public function consume()
    {
        $this->rabbitConnection->openConnection();

        echo '[*] Waiting for messages. To exit press CTRL+C' . PHP_EOL;

        $this->declareExchanges();

        $this->rabbitConnection
            ->getChannel()
            ->basic_qos(null, 1, null);

        $this->rabbitConnection
            ->getChannel()
            ->basic_consume($this->getQueueName(), '', false, false, false, false, $this->getCallback());

        while (
            $this->rabbitConnection
            ->getChannel()
            ->is_consuming()
        ) {
            $this->rabbitConnection
                ->getChannel()
                ->wait();
        }

        $this->rabbitConnection->closeConnection();
    }

    protected function getCallback(): callable
    {
        return function ($msg) {
            $data = json_decode($msg->body, true);

            $is_dead = !$data
                || !isset($data['start'])
                || (time() - $data['start']) > Constants::MOVE_MESSAGE_TO_DEAD_TIME;

            // Если с момента публикации сообщения прошло больше чем MOVE_MESSAGE_TO_DEAD_TIME секунд
            // перемещаем сообщение в мертвую очередь
            if ($is_dead) {
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                $msg_dead = new AMQPMessage($msg->body, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

                $msg->delivery_info['channel']->basic_publish(
                    $msg_dead,
                    'exchange_dead',
                    $msg->delivery_info['exchange'] . '_routing_dead'
                );
            } else {
                $res = $this->processMessage($data['data']);

                if ($res) {
                    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                } else {
                    $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, false);
                }
            }
        };
    }

    abstract protected function processMessage(array $data): bool;

    abstract protected function getExchangeName(): string;

    abstract protected function getQueueName(): string;

    private function declareExchanges()
    {
        $this->declareExchangeStrategy->declare(
            $this->rabbitConnection->getChannel(),
            $this->getExchangeName(),
            $this->getQueueName()
        );
    }
}
