<?php

namespace Swisssms;

use Illuminate\Support\Str;

class SmsMessage
{
    protected $data = [];

    public $body;
    public $message;
    public $to;
    public $from;
    public $sender;
    public $place;

    public function content($msg): SmsMessage
    {
        if(!isset($this->data['message'])) {
            $this->data['message'] = $msg;
        }else{
            $this->data['message'] .= ' '.$msg;
        }

        $this->body = $this->message = $this->data['message'];
        return $this;
    }

    public function sender($sender): SmsMessage
    {
        if(!isset($this->data['sender'])){
            $this->data['sender'] = Str::limit($sender, 10, '');
        }
        $this->sender = $this->from = $this->data['sender'];
        return $this;
    }

    public function from($sender): SmsMessage
    {
        $this->sender($sender);
        $this->from = $this->sender;
        return $this;
    }

    public function to($to): SmsMessage
    {
        if(!isset($this->data['to'])){
            $this->data['to'] = $to;
        }
        $this->to = $this->data['to'];
        return $this;
    }

    public function place(bool $place): SmsMessage
    {
         $this->place = $place;
         return $this;
    }

    public function get(): array
    {
        return $this->data;
    }
}
