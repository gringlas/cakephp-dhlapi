<?php
namespace DHLApi\Lib\Requests;
use Cake\Chronos\Chronos;

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
    protected $response;


    public function __construct($data, $config)
    {
        $this->data = $data;
        $this->config = $config;
        $this->setRequest();
    }

    public abstract function setRequest();
    public abstract function callApi();

    public function getIsError()
    {
        return $this->isError;
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
                    <MessageReference>Esteemed Courier Service of DHL</MessageReference>
                    <SiteID>'.$this->config['siteID'].'</SiteID>
                    <Password>'.$this->config['password'].'</Password>
                </ServiceHeader>
            </Request>';
    }
}
