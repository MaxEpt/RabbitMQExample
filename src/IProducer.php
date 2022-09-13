<?php

namespace RabbitMQExample;

interface IProducer
{
    public function produce(): bool;
}
