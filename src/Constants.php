<?php

namespace RabbitMQExample;

final class Constants
{
    public const DEFAULT_HEARTBEAT         = 300;
    public const DEFAULT_RETRY_MESSAGE_TTL = 900000;
    public const MOVE_MESSAGE_TO_DEAD_TIME = 14400;
}
