<?php

namespace Swisssms\Drivers;

class NigeriaBulkSmsDotCom extends BaseDriver
{
    /**
     * @var
     */
    private $tos;

    /**
     * @var
     */
    private $message;

    /**
     * @var
     */
    private $sender;

    /**
     * @var
     */
    private $url;

    /**
     * @var
     */
    private $username;

    /**
     * @var
     */
    private $password;

    /**
     * @param array $paramArr
     * @param array $config
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
        $this->tos      = $paramArr['to'];
        $this->message  = $paramArr['message'];
        $this->sender   = $paramArr['sender'];

        $this->url      = $config['url'];
        $this->username = $config['auth']['username'];
        $this->password = $config['auth']['password'];
    }


    protected function _initiateSMS()
    {
        $isError = 0;
        $errorMessage = true;

        //Preparing post parameters
        $pd = [
            'username'  => urlencode($this->username),
            'password'  => urlencode($this->password),
            'message'   => $this->message,
            'sender'    => $this->sender,
            'mobiles'   => implode(',',$this->tos),
            'verbose'   =>  'true'
        ];

        $url = $this->url."?username={$pd['username']}&password={$pd['password']}&message={$pd['message']}&sender={$pd['sender']}&mobiles={$pd['mobiles']}&verbose={$pd['verbose']}";

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
