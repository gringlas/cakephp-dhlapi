<?php
namespace DHLApi\Lib\Requests;
use Cake\Chronos\Chronos;
use Cake\Core\App;
use Cake\Filesystem\File;
use Cake\I18n\FrozenTime;

/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 02.04.17
 * Time: 17:20
 */
abstract class DHLApiRequest
{

    protected $request;
    protected $data;

    /**
     * @var $config
     *
     * Configuration array needs the following keys:
     * 'uri' => URI to DHL XML webservice
     * 'siteID' => DHL siteID
     * 'password' => DHL password
     * 'accountNumber' => DHL accountnumber
     *
     */
    protected $config;

    protected $isError = false;
    protected $errorMessage = "";
    protected $response = [];


    public function __construct($data, $config)
    {
        $this->data = $data;
        $this->config = $config;
        $this->ensureConfig();
        $this->setRequest();
    }

    public abstract function setRequest();
    public abstract function callApi();


    public function getIsError()
    {
        return $this->isError;
    }


    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getResponse()
    {
        $response = [
            'isError' => $this->isError,
            'errorMessages' => [
                $this->errorMessage
            ]
        ];
        return array_merge($response, $this->response);
    }


    public function getRequest()
    {
        return $this->request;
    }


    public function setRequestAttribute()
    {
        $now = Chronos::now();
        return '
            <Request>
                <ServiceHeader>
                    <MessageTime>'.$now->format("Y-m-d\TH:m:s.000-08:00").'</MessageTime>
                    <MessageReference>'.$this->config['messageReference']. '</MessageReference>
                    <SiteID>'.$this->config['siteID'].'</SiteID>
                    <Password>'.$this->config['password'].'</Password>
                </ServiceHeader>
            </Request>';
    }


    private function ensureConfig()
    {
        $this->config['messageReference'] = $this->ensureMessageReferenceLengthBetween28And32();
    }

    /**
     * according to DHL Document XMLServices5.2_Pirckup.pdf (page 10) a MessageReference should be between 28 ands 32 characters
     */
    private function ensureMessageReferenceLengthBetween28And32()
    {
        if (!isset($this->config['messageReference'])) {
            $messageReference = "CakePHP DHL Api " . time();
        } else {
            $messageReference = $this->config['messageReference'] . " " . time();
        }
        if (strlen($messageReference) < 28) {
            $messageReference = str_pad($messageReference,28,"#");
        }
        return substr($messageReference, 0, 32);
    }


    /**
     * ShipmentLabel sends back UTF-8 broken XML documents, even if correctly utf-8 encoded requests are made,
     * when trying to use names with umlauts, so this function could replace those.
     *
     * @param $string
     * @return mixed
     */
    protected function replaceUmlauts($string)
    {
        return str_replace(['ü','Ü','ä','Ä','ö','Ö','ß', '–'],['u','U','a','A','o','O','s', '-'], $string);
    }


    /**
     * If an error during a DHL API request occurs this function can be called to log request and if given the response.
     * As the design of this Plugin is not that advanced this function has to be called manualy :(
     */
    protected function errorRequestAndResponseToFile()
    {
        $time = new FrozenTime();
        $class = explode('\\', get_class($this))[sizeof(explode('\\', get_class($this))) - 1];
        $filename = $class . '_' . $time->format('Y-m-d_H:i') . '.xml';
        $errorRequestFile = new File(LOGS . 'api_errors' . DS . 'request' . DS . $filename, true);
        $errorRequestFile->write($this->request);
        $errorRequestFile->close();
        if ($this->dhlResponseBody) {
            $errorResponseFile = new File(LOGS . 'api_errors' . DS . 'response' . DS . $filename, true);
            $errorResponseFile->write($this->dhlResponseBody);
            $errorResponseFile->close();
        }

    }
}
