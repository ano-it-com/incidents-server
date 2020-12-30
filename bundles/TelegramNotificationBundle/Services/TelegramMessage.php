<?php


namespace TelegramNotificationBundle\Services;


class TelegramMessage implements TelegramMessageInterface
{
    protected $message;

    protected $meta;

    public function __construct($message, array $meta)
    {
        $this->message = $message;
        foreach ($meta as $key => $value) {
            $this->addMeta($key, $value);
        }
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    public function render()
    {
        $text = "";
        foreach ($this->meta as $title => $line) {
            $text .= "<b>{$title}:</b> {$line}" . PHP_EOL;
        }

        if (strlen($this->message) > 0) {
            return $text . $this->message;
        }
        return $text;
    }

    public function getParseMode()
    {
        return 'html';
    }
}