<?php

namespace Swisssms;

class SmsLib
{
    protected $driver_object;
    private $_setDriver = null;

    /**
     * @param null $driver
     * @return $this
     */
    public function setDriver($driver = null): SmsLib
    {
        $this->_setDriver = $driver;
        return $this;
    }

    //@return mix
    public function send(Array $paramArr)
    {
        $driver = $this->_initDriver();

        $this->driver_object = (new SmsDriverClassMap())->getDriver($driver);

        return (new $this->driver_object)
            ->boot(
                $this->_makeSureToIsArray($paramArr),
                config('bcsmschannel.drivers.'.$driver)
            );
    }

    /**
     * @param $paramArr
     * @return mixed
     */
    private function _makeSureToIsArray($paramArr)
    {
        $paramArr['to'] = (is_array($paramArr['to']))
            ? $paramArr['to']
            : [$paramArr['to']];

        return $paramArr;
    }

    /**
     * @return string
     */
    private function _initDriver(): string
    {
        return $this->_setDriver ?? config('bcsmschannel.default_driver');
    }

}
