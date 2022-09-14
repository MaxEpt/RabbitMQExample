<?php

namespace RabbitMQExample;

class SimpleConsumer extends AbstractConsumer
{
    protected function processMessage(array $data): bool
    {
        // нужные действия с данными, в результате вернуть bool
        return true;
    }

    protected function getExchangeName(): string
    {
        return 'SimpleExchange';
    }

    protected function getQueueName(): string
    {
        return 'simple';
    }
}