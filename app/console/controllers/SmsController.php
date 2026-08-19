<?php

namespace console\controllers;

use common\services\SmsSender;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class SmsController extends Controller
{
    private $queue = 'sms_queue';

    /**
     * Запуск consumer'а для обработки SMS из очереди RabbitMQ
     */
    public function actionConsume(): int
    {
        $this->stdout("Starting SMS consumer...\n");
        
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $port = (int)(getenv('RABBITMQ_PORT') ?: 5672);

        try {
            $connection = new AMQPStreamConnection($host, $port, 'guest', 'guest');
            $channel = $connection->channel();
            $channel->queue_declare($this->queue, false, true, false, false);

            $this->stdout(" [*] Waiting for messages in {$this->queue}. To exit press CTRL+C\n");

            $callback = function ($msg) {
                $this->handleMessage($msg);
            };

            $channel->basic_consume($this->queue, '', false, false, false, false, $callback);

            while ($channel->is_consuming()) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();

        } catch (\Exception $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    private function handleMessage(AMQPMessage $msg): void
    {
        $data = json_decode($msg->body, true);
        
        if (!$data || !isset($data['type'])) {
            $this->stderr("Invalid message format: " . $msg->body . "\n");
            $msg->ack();
            return;
        }

        $this->stdout(" [x] Received: " . $msg->body . "\n");

        try {
            $sender = new SmsSender();
            
            switch ($data['type']) {
                case 'new_book':
                    $this->handleNewBook($data, $sender);
                    break;
                case 'new_subscription':
                    $this->handleNewSubscription($data, $sender);
                    break;
                default:
                    $this->stderr("Unknown message type: " . $data['type'] . "\n");
            }

            $msg->ack();
            $this->stdout(" [✓] Message processed successfully\n");

        } catch (\Exception $e) {
            $this->stderr("Error processing message: " . $e->getMessage() . "\n");
            // Можно добавить retry логику здесь
            $msg->ack(); // Ack чтобы не зациклить
        }
    }

    private function handleNewBook(array $data, SmsSender $sender): void
    {
        $bookId = $data['book_id'] ?? null;
        $phones = $data['phones'] ?? [];

        if (!$bookId || empty($phones)) {
            return;
        }

        $book = \common\models\Book::findOne($bookId);
        if (!$book) {
            $this->stderr("Book not found: {$bookId}\n");
            return;
        }

        $message = "Новая книга: \"{$book->title}\" ({$book->year} год)";

        foreach ($phones as $phone) {
            $sender->send($phone, $message);
            $this->stdout("   → SMS sent to {$phone}\n");
        }
    }

    private function handleNewSubscription(array $data, SmsSender $sender): void
    {
        $authorId = $data['author_id'] ?? null;
        $phone = $data['phone'] ?? null;

        if (!$authorId || !$phone) {
            return;
        }

        $author = \common\models\Author::findOne($authorId);
        if (!$author) {
            $this->stderr("Author not found: {$authorId}\n");
            return;
        }

        $message = "Вы успешно подписались на автора \"{$author->name}\". Вы будете получать уведомления о новых книгах.";
        $sender->send($phone, $message);
        $this->stdout("   → Welcome SMS sent to {$phone}\n");
    }
}