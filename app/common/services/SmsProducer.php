<?php

namespace common\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

class SmsProducer
{
    private $connection;
    private $channel;
    private $queue = 'sms_queue';

    public function __construct()
    {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = (int)(getenv('RABBITMQ_PORT') ?: 5672);

        $this->connection = new AMQPStreamConnection($host, $port, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    public function notifyAboutNewBook(int $bookId, array $authorIds, array $phones): void
    {
        $this->publish([
            'type' => 'new_book',
            'book_id' => $bookId,
            'author_ids' => $authorIds,
            'phones' => $phones,
            'created_at' => time(),
        ]);
    }

    public function notifyAboutNewSubscription(int $authorId, string $phone): void
    {
        $this->publish([
            'type' => 'new_subscription',
            'author_id' => $authorId,
            'phone' => $phone,
            'created_at' => time(),
        ]);
    }

    private function publish(array $data): void
    {
        $msg = new AMQPMessage(
            json_encode($data, JSON_UNESCAPED_UNICODE),
            ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($msg, '', $this->queue);
        Yii::info("Published to {$this->queue}: " . json_encode($data), 'rabbitmq');
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}