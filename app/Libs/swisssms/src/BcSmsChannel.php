<?php

namespace Swisssms;

use Swisssms\Exceptions\BcSmsException;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BcSmsChannel
{
    private SmsMessage $smsMessage;

    private string $defaultPlace = 'Nga';

    private array $config = [];

    /**
     * Send the given notification.
     *
     * @param $notifiable
     * @param Notification $notification
     * @return void
     * @throws BcSmsException
     */
    public function send($notifiable, Notification $notification)
    {
        $this->config = config('bcsmschannel');

        $this->_build($notifiable, $notification);

        if (!$this->config['is_enabled']) {
            $this->_mock();
        } else {
            // Send notification to the $notifiable instance...
            $this->_process();
        }
    }

    /**
     * @param $notifiable
     * @param $notification
     * @throws BcSmsException
     */
    private function _build($notifiable, $notification)
    {
        $this->smsMessage = $notification->toSms($notifiable);

        try {
            $this->smsMessage->to($this->getTo($notifiable));
        } catch (BcSmsException $e) {
            echo $e->getMessage() . "\n";
            throw $e;
        }

        //Lets get Sender
        $this->smsMessage->sender($this->getSender($notifiable));

        //Add prefix
        $this->smsMessage->message =
            $this->smsMessage->place->getPrefixMsg() . $this->smsMessage->message;

        //Add suffix
        $this->smsMessage->message .=  $this->smsMessage->place->getSuffixMsg();
    }

    /**
     * @param $notifiable
     * @return mixed
     * @throws BcSmsException
     */
    protected function getTo($notifiable)
    {
        $phoneNumber = $this->smsMessage->to;

        if (!$phoneNumber) {

            // THIS IS FROM TEMPUSERS TABLE
            if (isset($notifiable->phone)) {
                $phoneNumber = $notifiable->phone;
            }

            // THIS IS FROM THE CURRENT MODEL
            if (method_exists($notifiable, 'routeNotificationSmsPhone')) {
                $phoneNumber = $notifiable->routeNotificationSmsPhone($notifiable);
            }
        }

        if ($phoneNumber && method_exists($notifiable, 'routeNotificationSmsCountry')) {
            $placeClassName = $notifiable->routeNotificationSmsCountry($notifiable);
            $class = $this->placeNameSpace() . '\\' . Str::of($placeClassName)->lower()->ucfirst();

            if (!class_exists($class)) {
                //Fallback to Aus Place
                $class = $this->fallBackPlace();
            }
        } else {
            $class = $this->fallBackPlace();
        }

        $this->smsMessage->place = (new $class());

        if ($this->smsMessage->place->isActive() && $phoneNumber) {
            return $this->smsMessage->place->make($phoneNumber);
        }

        throw new BcSmsException('Notification: Invalid sms phone number');
    }

    /**
     * @return string
     */
    protected function placeNameSpace(): string
    {
        return __NAMESPACE__ . "\\Places";
    }

    /**
     * @return string
     */
    protected function fallBackPlace(): string
    {
        return $this->placeNameSpace() . '\\' . $this->defaultPlace;
    }

    /**
     * Get the alphanumeric sender.
     *
     * @param $notifiable
     * @return mixed|null
     */
    protected function getSender($notifiable)
    {

        if (($from = $this->smsMessage->place->getFrom())) {
            return $from;
        }

        if (($from = $this->smsMessage->sender)) {
            return $from;
        }

        if (method_exists($notifiable, 'routeNotificationSmsSender')) {
            return $notifiable->routeNotificationSmsSender();
        }

        return $this->config['sender'];
    }

    private function _process(): void
    {
        (new SmsLib)
            ->setDriver($this->smsMessage->place->driver())
            ->send($this->smsMessage->get());
    }

    private function _mock()
    {
        if (App::environment('local')) {
            $buildSMS  = "From: " . $this->smsMessage->from . "\n";
            $buildSMS .= "To: " . $this->smsMessage->to . "\n";
            $buildSMS .= "Message: " . $this->smsMessage->message . "\n";
            $buildSMS .= "============================================\n";
            $buildSMS .= "BUILD INFO: (Not included in the actual SMS):\n";
            $buildSMS .= "============================================\n";
            $buildSMS .= "Phone Code: " . $this->smsMessage->place->getPhoneCode() . "\n";
            $buildSMS .= "Driver: " . $this->smsMessage->place->driver() . "\n";
            $buildSMS .= "Driver class: " . $this->smsMessage->place->getDriverClass() . "\n";

            $mockMethod = 'mockType' . Str::studly($this->config['mock_type']);
            $this->$mockMethod($buildSMS);
        }
    }

    /**
     * @param $buildSMS
     */
    private function mockTypeLog($buildSMS)
    {
        $this->_prepareLogFile();
        Log::channel('smsmock')
            ->info("Mock: SMS [" . $this->smsMessage->to . "] \n" . $buildSMS . "\n");
    }

    /*
     * @return void
     */
    private function _prepareLogFile(): void
    {
        $hasLogFile = config('logging.channels.smsmock');

        if (!$hasLogFile) {

            $existingConfig = config('logging.channels');
            $existingConfig['smsmock'] =  [
                'driver' => 'single',
                'path' => storage_path('logs/smsmock.log'),
                'level' => 'debug',
            ];

            config(['logging.channels' => $existingConfig]);
        }

        $file = config('logging.channels.smsmock.path');
        if (!File::exists($file)) {
            File::put($file, '');
        }
    }

    /**
     * @param $buildSMS
     */
    private function mockTypeMail($buildSMS)
    {
        Mail::raw($buildSMS, function ($message) {
            $message->to([
                $this->config['mock_to']
            ])->subject('Mock: SMS [' . $this->smsMessage->to . ']');
        });
    }
}
