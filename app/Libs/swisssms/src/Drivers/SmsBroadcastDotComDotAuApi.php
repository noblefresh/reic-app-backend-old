<?php


namespace Swisssms\Drivers;


use Illuminate\Support\Str;

class SmsBroadcastDotComDotAuApi extends BaseDriver
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
    private $username;

    /**
     * @var
     */
    private $password;

    /**
     * @var
     */
    private $url;

    /**
     * @param array $paramArr
     * @param array $config
     * @return void
     */
    public function boot(Array $paramArr, array $config)
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
        $this->username = $config['auth']['username'];
        $this->password = $config['auth']['password'];
    }

    protected function _initiateSMS(): void
    {
        $username = $this->username;
        $password = $this->password;
        $destination = implode(',', $this->to);

        $source = $this->from;
        $text = $this->body;
        $ref = Str::random(10);

        $content =  'username='.rawurlencode($username).
            '&password='.rawurlencode($password).
            '&to='.rawurlencode($destination).
            '&from='.rawurlencode($source).
            '&message='.rawurlencode($text).
            '&ref='.rawurlencode($ref);

        $smsbroadcast_response = $this->sendSMS($content);
        $response_lines = explode("\n", $smsbroadcast_response);

        foreach( $response_lines as $data_line){

            $message_data = explode(':',$data_line);
            if($message_data[0] == "OK"){
                echo "The message to ".$message_data[1]." was successful, with reference ".$message_data[2]."\n";
            }elseif( $message_data[0] == "BAD" ){
                echo "The message to ".$message_data[1]." was NOT successful. Reason: ".$message_data[2]."\n";
            }elseif( $message_data[0] == "ERROR" ){
                echo "There was an error with this request. Reason: ".$message_data[1]."\n";
            }
        }
    }

    protected function sendSMS($content) {
        $isError = 0;
        $errorMessage = true;

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec ($ch);

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




