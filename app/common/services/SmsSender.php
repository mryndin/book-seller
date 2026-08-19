<?php

namespace common\services;

use Yii;

use function getenv;
use function json_encode;
use function curl_init;
use function curl_setopt;
use function curl_exec;
use function curl_getinfo;
use function curl_error;
use function curl_close;

/**
 * SmsSender is responsible for sending SMS messages using the SMSPilot API.
 */
class SmsSender
{

    /**
     * @var string The URL of the SMSPilot API.
     */
    private $apiUrl = 'https://smspilot.ru/api.php';

    /**
     * @var string The API key for the SMSPilot service.
     */
    private $apiKey;

    /**
     * @var string The sender name for the SMS messages.
     */
    private $senderName;

    /**
     * Initializes the SmsSender with the API key and sender name from the application parameters.
     */
    public function __construct()
    {
        $this->apiKey = Yii::$app->params['smspilot']['api_key'] ?? '';
        $this->senderName = Yii::$app->params['smspilot']['sender_name'] ?? 'BookSeller';
    }

    /**
     * Sends an SMS message to the specified phone number.
     *
     * @param string $phone The recipient's phone number.
     * @param string $message The message content.
     * @return bool True if the message was sent successfully, false otherwise.
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Yii::warning("SMS API key not configured. Message: {$phone} - {$message}", 'sms');
            return false;
        }

        $params = [
            'send' => $message,
            'to' => $phone,
            'from' => $this->senderName,
            'apikey' => $this->apiKey,
            'format' => 'json',
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Yii::error("SMS sending failed (cURL error): {$error}", 'sms');
            return false;
        }

        if ($httpCode !== 200) {
            Yii::error("SMS sending failed (HTTP {$httpCode}): {$response}", 'sms');
            return false;
        }

        $result = json_decode($response, true);
        if (!$result || isset($result['error'])) {
            Yii::error("SMS sending failed: " . json_encode($result), 'sms');
            return false;
        }

        Yii::info("SMS sent successfully to {$phone}", 'sms');
        return true;
    }
}