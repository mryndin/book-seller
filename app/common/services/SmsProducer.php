<?php

namespace common\services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;

/**
 * SmsProducer is responsible for publishing messages to the RabbitMQ queue for SMS notifications.
 */
class SmsProducer
{
    /**
     * @var AMQPStreamConnection The RabbitMQ connection.
     */
    private $connection;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel The RabbitMQ channel.
     */
    private $channel;

    /**
     * @var string The name of the RabbitMQ queue.
     */
    private $queue = 'sms_queue';

    /**
     * Initializes the RabbitMQ connection and channel.
     */
    public function __construct()
    {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = (int)(getenv('RABBITMQ_PORT') ?: 5672);

        $this->connection = new AMQPStreamConnection($host, $port, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    /**
     * Notify about a new book to the specified authors and their phone numbers.
     *
     * @param int $bookId The ID of the new book.
     * @param array $authorIds The IDs of the authors to notify.
     * @param array $phones The phone numbers to notify.
     */
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

    /**
     * Notify about a new subscription to the specified author and phone number.
     *
     * @param int $authorId The ID of the author.
     * @param string $phone The phone number to notify.
     */
    public function notifyAboutNewSubscription(int $authorId, string $phone): void
    {
        $this->publish([
            'type' => 'new_subscription',
            'author_id' => $authorId,
            'phone' => $phone,
            'created_at' => time(),
        ]);
    }

    /**
     * Publishes a message to the RabbitMQ queue.
     *
     * @param array $data The data to publish.
     */
    private function publish(array $data): void
    {
        $msg = new AMQPMessage(
            json_encode($data, JSON_UNESCAPED_UNICODE),
            ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($msg, '', $this->queue);
        Yii::info("Published to {$this->queue}: " . json_encode($data), 'rabbitmq');
    }

    /**
     * Closes the RabbitMQ channel and connection when the object is destroyed.
     */
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