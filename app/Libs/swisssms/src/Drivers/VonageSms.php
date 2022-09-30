<?php

//Vonage is formerly known as Nexmo

namespace Swisssms\Drivers;

use Swisssms\Exceptions\BcSmsException;

class VonageSms extends BaseDriver
{

    /**
     * @var
     */
    private $to;

    /**
     * @var
     */
    private $body;

    /**
     * @var
     */
    private $from;

    /**
     * @var
     */
    private $client;


    /**
     * @inheritDoc
     * @throws BcSmsException
     */
    public function boot(array $paramArr, array $config)
    {
        $this->setUp($paramArr, $config);
        $this->_initiateSMS();
    }

    /**
     * @inheritDoc
     * @throws BcSmsException
     */
    protected function setUp(array $paramArr, array $config)
    {
        //First check if vonage composer package has been installed
        $this->checkDependencies(
            'Vonage\Client\Credentials\Basic',
            'composer require vonage/client'
        );

        $this->to       = $paramArr['to'];
        $this->body     = $paramArr['message'];
        $this->from     = $paramArr['sender'];

        $basic  = new \Vonage\Client\Credentials\Basic($config['auth']['api_key'], $config['auth']['api_secret']);
        $this->client = new \Vonage\Client($basic);

    }

    protected function _initiateSMS()
    {
        $response = $this->client->sms()->send(
            new \Vonage\SMS\Message\SMS($this->to, $this->from, $this->body)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            echo "The message was sent successfully\n";
        } else {
            echo "The message failed with status: " . $message->getStatus() . "\n";
        }

    }
}
