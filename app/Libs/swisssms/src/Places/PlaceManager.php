<?php


namespace Swisssms\Places;


use Swisssms\SmsDriverClassMap;
use Illuminate\Support\Str;

abstract class PlaceManager
{
    /**
     * @var $phoneCode
     */
    protected $phoneCode;

    /**
     * @var $from
     */
    protected $from;

    /**
     * @return string|null
     */
    public function driver()
    {
        return config('bcsmschannel.default_driver');
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @param $phone
     * @param $countryCode
     * @return string
     */
    protected function handle($phone, $countryCode): string
    {
        return $this->formatPhoneNumber($phone, $countryCode);
    }

    /**
     * @param $phone
     * @param $countryCode
     * @return string
     */
    protected function formatPhoneNumber($phone, $countryCode): string
    {
        //Is leading zero?
        if (Str::startsWith($phone, '0')) {
            //Removing the first zero in the phone number
            $phone = Str::replaceFirst('0', '', $phone);
        }

        //Does the number start with +
        if (Str::startsWith($phone, '+')) {
            //Removing the + in the phone number
            return $phone;
        }

        return '+' . $countryCode . $phone;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setPhoneCode($code): PlaceManager
    {
        $this->phoneCode = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getPhoneCode(): int
    {
        return $this->phoneCode;
    }

    /**
     * @return string
     */
    public function getDriverClass(): string
    {
        return (new SmsDriverClassMap())->getDriver($this->driver());
    }

    public function setFrom(): ?string
    {
        return null;
    }

    public function getFrom(): ?string
    {
        return $this->from = $this->setFrom();
    }

    public function setPrefixMsg(): ?string
    {
        return null;
    }

    public function setSuffixMsg(): ?string
    {
        return null;
    }

    public function getPrefixMsg(): ?string
    {
        if (($msg = $this->setPrefixMsg())) {
            return $msg . ': ';
        }

        return null;
    }

    public function getSuffixMsg(): ?string
    {
        if (($msg = $this->setSuffixMsg())) {
            return ' :' . $msg;
        }

        return null;
    }
}
