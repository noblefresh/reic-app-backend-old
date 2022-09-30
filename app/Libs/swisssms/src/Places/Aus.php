<?php


namespace Swisssms\Places;


use Swisssms\Drivers\SmsBroadcastDotComDotAuApi;

class Aus extends PlaceManager implements PlaceInterface
{

    /**
     * Aus constructor.
     * @param int $code
     */
    public function __construct(int $code=61)
    {
        $this->phoneCode = $code;
    }

    /**
     * @param string $phone
     * @return string
     */
    public function make(string $phone): string
    {
        return $this->handle($phone, $this->phoneCode);
    }

    /**
     * @return string
     */
    public function driver(): string
    {
        return SmsBroadcastDotComDotAuApi::class;
    }


}
