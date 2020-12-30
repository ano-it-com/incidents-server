<?php

namespace TelegramNotificationBundle\Services\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use TelegramNotificationBundle\Services\TelegramMessageInterface;
use Throwable;

class TelegramHttpClient implements TelegramClientInterface
{
    /** @var ClientInterface */
    protected $client;

    /** @var string */
    protected $token;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct($url, $token, LoggerInterface $logger)
    {
        $this->client = new Client([
            'base_uri' => $url
        ]);
        $this->token = $token;
        $this->logger = $logger;
    }

    protected function request($method, $url, array $params)
    {
        try {
            $response = $this->client->request($method, "/bot{$this->token}$url", ['form_params' => $params]);
            return $response->getStatusCode() == 200;
        } catch (Throwable $exception) {
            $this->logger->error("[Telegram]: {$exception->getMessage()}");
        }
        return false;
    }

    public function send(TelegramMessageInterface $message, int $chatId): bool
    {
        return $this->request('POST', '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $message->render(),
            'parse_mode' => $message->getParseMode(),
        ]);
    }
}