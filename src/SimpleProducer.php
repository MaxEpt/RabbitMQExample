<?php

namespace RabbitMQExample;

class SimpleProducer extends AbstractProducer
{
    protected function getExchangeName(): string
    {
        return 'example';
    }
}