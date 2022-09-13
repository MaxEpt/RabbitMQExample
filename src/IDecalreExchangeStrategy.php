<?php

namespace RabbitMQExample;

interface IDeclareExchangeStrategy
{
    /**
     * @param $rabbitConnectionChannel
     * @param string $exchangeName
     * @param $mainQueueName
     * @return void
     */
    public function declare($rabbitConnectionChannel, string $exchangeName, string $mainQueueName): void;
}
