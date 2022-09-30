<?php

namespace Swisssms\Drivers;

use Illuminate\Support\Str;
use Swisssms\Exceptions\BcSmsException;


class TermiiSms extends BaseDriver
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
    private $api_key;

    /**
     * @var
     */
    private $channel = 'generic';

    /**
     * @param array $paramArr
     * @param array $config
     * @return void
     * @throws BcSmsException
     */
    public function boot(array $paramArr, array $config): void
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
    protected function setUp(array $paramArr, array $config): void
    {
        //First check if vonage composer package has been installed
        $this->checkDependencies(
            'Okolaa\TermiiPHP\Termii',
            'composer require okolaa/termiiphp'
        );

        $this->to       = $this->prepareNumber($paramArr['to']);
        $this->body     = $paramArr['message'];
        $this->from     = $config['sender_id'];

        // Your Account SID and Auth Token from twilio.com/console
        $this->api_key = $config['auth']['api_key'];
        $this->channel = $config['channel'];
        //$this->client = new Termii($from, $api_key);
        //$this->client->setChannel($config['channel']);
    }

    private function prepareNumber($to): string
    {
        $to = (string)$to[0];

        //dd(Str::startsWith($to,'0'));

        if(Str::startsWith($to, '234') && strlen($to) === 13) {
            return $to;
        }

        if(Str::startsWith($to, '0')) {
            return Str::replaceFirst('0', '234', $to);
        }

        return str_replace('+', '', $to);

    }

    protected function _initiateSMS()
    {
        // Use the client to do fun stuff like send text messages!
        /*$sent = $this->client->sendMessage([
                "phone_number"  => $this->to,
                "message"       => $this->body
            ]);*/


        $curl = curl_init();
        $data = [
            "api_key"   => $this->api_key,
            "to"        => $this->to,
            "from"      => $this->from,
            "sms"       => $this->body,
            "type"      => "plain",
            "channel"   => $this->channel
        ];

        //dump($data);

        $post_data = json_encode($data);

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ng.termii.com/api/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        //echo $response;


    }

}
