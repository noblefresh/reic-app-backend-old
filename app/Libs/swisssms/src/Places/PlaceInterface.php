<?php


namespace Swisssms\Places;



interface PlaceInterface
{
    /**
     * @return bool
     */
    function isActive(): bool;

    /**
     * @param $code
     * @return PlaceManager
     */
    function setPhoneCode($code): PlaceManager;

    /**
     * @return int
     */
    function getPhoneCode(): int;

    /**
     * @return string
     */
    function getDriverClass(): string;

    /**
     * @param string $phone
     * @return string
     */
    function make(string $phone): string;

    /**
     * Sms gateway sdk to use
     * @return string | null
     */
    function driver();

    /**
     * Overwrite all others from
     * @return mixed
     */
    function setFrom(): ?string;

    /**
     * Add message to the start of the sms
     * @return string
     */
    function setPrefixMsg(): ?string;

    /**
     * Add message to the end of the sms
     * @return string
     */
    function setSuffixMsg(): ?string;

    /**
     * Add message to the start of the sms
     * @return string | null
     */
    function getPrefixMsg(): ?string;

    /**
     * Add message to the end of the sms
     * @return string | null
     */
    function getSuffixMsg(): ?string;
}
