<?php

namespace TelegramNotificationBundle\Services\Client;


use TelegramNotificationBundle\Services\TelegramMessageInterface;

class TelegramFileClient implements TelegramClientInterface
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        if (!file_exists($this->filePath)) {
            mkdir($this->filePath, 0777, true);
        }
    }

    public function send(TelegramMessageInterface $message, int $chatId): bool
    {
        $file = $this->filePath . DIRECTORY_SEPARATOR . $chatId . ".chat";
        $date = date("Y-m-d H:i:s");

        $render = $message->render();
        if(substr($render, -1) == PHP_EOL){
            $render = substr($render, 0, -1);
        }

        $text = "#################     start message     #################" . PHP_EOL;
        $text .= "################## {$date} ##################" . PHP_EOL;
        $text .= $render . PHP_EOL;
        $text .= "###################### end message ######################" . PHP_EOL . PHP_EOL;

        return file_put_contents($file, $text, FILE_APPEND);
    }
}