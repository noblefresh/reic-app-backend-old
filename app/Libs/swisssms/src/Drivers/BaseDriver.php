<?php

namespace Swisssms\Drivers;

use Swisssms\Exceptions\BcSmsException;
use Illuminate\Support\Str;

abstract class BaseDriver
{

    /**
     * @param array $paramArr
     * @param array $config
     * @return mixed
     */
    abstract public function boot(array $paramArr, array $config);

    /**
     * @param array $paramArr
     * @param array $config
     * @return mixed
     */
    abstract protected function setUp(array $paramArr, array $config);

    abstract protected function _initiateSMS();

    /**
     * @param string $className
     * @param string $composerClass
     * @throws BcSmsException
     */
    protected function checkDependencies(string $className, string $composerClass)
    {
        if(!class_exists($className)) {
            $driverName = Str::before($className, '\\');
            throw new BcSmsException('Notification: '.$driverName.' driver is missing required dependencies. RUN: & '.$composerClass);
        }
    }

}
