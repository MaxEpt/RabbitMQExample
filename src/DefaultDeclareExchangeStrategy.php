<?php

namespace RabbitMQExample;

final class DefaultDeclareExchangeStrategy
{
    public function declare($rabbitConnectionChannel, string $exchangeName, string $mainQueueName)
    {
        $exchange_name_retry = $exchangeName . '_retry';
        $queue_name_retry = $mainQueueName . '_retry';
        $queue_name_dead = $mainQueueName . '_dead';

        //Рабочая очередь
        $rabbitConnectionChannel->exchange_declare($exchangeName, 'direct', false, true, false);

        $rabbitConnectionChannel->queue_declare($mainQueueName, false, true, false, false, false, [
            'x-dead-letter-exchange' => ['S', $exchange_name_retry],
        ]);
        $rabbitConnectionChannel->queue_bind($mainQueueName, $exchangeName);

        //Повторное отправление
        $rabbitConnectionChannel->exchange_declare($exchange_name_retry, 'direct', false, true, false);
        $rabbitConnectionChannel->queue_declare($queue_name_retry, false, true, false, false, false, [
            'x-message-ttl' => ['I', Constants::DEFAULT_RETRY_MESSAGE_TTL],
            'x-dead-letter-exchange' => ['S', $exchangeName],
        ]);
        $rabbitConnectionChannel->queue_bind($queue_name_retry, $exchange_name_retry);

        //Мертвая очередь
        $rabbitConnectionChannel->exchange_declare('exchange_dead', 'direct', false, true, false);
        $rabbitConnectionChannel->queue_declare($queue_name_dead, false, true, false, false);
        $rabbitConnectionChannel->queue_bind(
            $queue_name_dead,
            'exchange_dead',
            $exchangeName . '_routing_dead'
        );
    }
}
