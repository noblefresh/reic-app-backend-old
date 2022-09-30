<?php

namespace Swisssms;


/*use Swisssms\Drivers\BulkSMSNigeriaDotCom;
use Swisssms\Drivers\NigeriaBulkSmsDotCom;
use Swisssms\Drivers\SmsBroadcastDotComDotAuApi;*/

class SmsDriverClassMap
{

    /**
     * @param $key
     * @return string
     */
    public function getDriver($key): string
    {
        return $this->fromConfig()[strtolower($key)]['class'];
    }


    private function fromConfig()
    {
        return config('bcsmschannel.drivers');
    }

    /**
     * @return string[]
     */
    /*protected function availMaps(): array
    {
        return [
            'nigeriabulksmsdotcom'      =>  NigeriaBulkSmsDotCom::class,
            'smsbroadcast'              =>  SmsBroadcastDotComDotAuApi::class,
            'bulksmsnigeriadotcom'      =>  BulkSMSNigeriaDotCom::class
        ];
    }*/
}
