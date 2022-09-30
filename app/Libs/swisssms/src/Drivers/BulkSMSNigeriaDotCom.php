<?php

namespace Swisssms\Drivers;

class BulkSMSNigeriaDotCom extends BaseDriver
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
    private $url;

    /**
     * @var
     */
    private $api_token;

    /**
     * @param array $paramArr
     * @param array $config
     * @return void
     */
    public function boot(array $paramArr, array $config)
    {
        $this->setUp($paramArr, $config);
        $this->_initiateSMS();
    }

    /**
     * @param array $paramArr
     * @param array $config
     */
    protected function setUp(array $paramArr, array $config)
    {
        $this->to       = $paramArr['to'];
        $this->body     = $paramArr['message'];
        $this->from     = $paramArr['sender'];

        $this->url      = $config['url'];
        $this->api_token = $config['auth']['api_key'];
    }

    protected function _initiateSMS()
    {
        $isError = 0;
        $errorMessage = true;

        //Preparing post parameters
        $pd = [
            'api_token'  => $this->api_token,
            'message'   => $this->body,
            'sender'    => $this->from,
            'mobiles'   => implode(',',$this->to),
            'dnd'   =>  2
        ];

        $url = $this->url."?api_token={$pd['api_token']}&body={$pd['message']}&from={$pd['sender']}&to={$pd['mobiles']}&dnd={$pd['dnd']}";

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //get response
        $output = curl_exec($ch);

        //Print error if any
        if (curl_errno($ch)) {
            $isError = true;
            $errorMessage = curl_error($ch);
        }
        curl_close($ch);

        if($isError){
            return ['error' => true , 'message' => $errorMessage];
        }else{
            return $output;
        }
    }

}
