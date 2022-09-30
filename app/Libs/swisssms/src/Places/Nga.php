<?php


namespace Swisssms\Places;


class Nga extends PlaceManager implements PlaceInterface
{

    /**
     * Nga constructor.
     * @param int $code
     */
    public function __construct(int $code=234)
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

}
