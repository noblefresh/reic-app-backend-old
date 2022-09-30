<?php

namespace Swisssms\Drivers;

use Swisssms\Exceptions\BcSmsException;

class TwilioSms extends BaseDriver
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
     * @param array $paramArr
     * @param array $config
     * @return void
     * @throws BcSmsException
     */
    public function boot(array $paramArr, array $config)
    {
        $this->setUp($paramArr, $config);
        $this->_initiateSMS();
    }

    /**
     * @param array $paramArr
     * @param array $config
     * @return void
     * @throws BcSmsException
     */
    protected function setUp(array $paramArr, array $config)
    {
        //First check if vonage composer package has been installed
        $this->checkDependencies(
            'Twilio\Rest\Client',
            'composer require twilio/sdk'
        );

        $this->to       = $paramArr['to'];
        $this->body     = $paramArr['message'];
        $this->from     = $paramArr['sender'];

        // Your Account SID and Auth Token from twilio.com/console
        $sid = $config['auth']['sid'];
        $token = $config['auth']['token'];
        $this->client = new \Twilio\Rest\Client($sid, $token);
    }

    protected function _initiateSMS()
    {
        // Use the client to do fun stuff like send text messages!
        $this->client->messages->create(
        // the number you'd like to send the message to
            $this->to,
            [
                // A Twilio phone number you purchased at twilio.com/console
                'from' => $this->from,
                // the body of the text message you'd like to send
                'body' => $this->body
            ]
        );
    }
}
