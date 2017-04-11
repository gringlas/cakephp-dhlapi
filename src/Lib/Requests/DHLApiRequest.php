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
    protected $config;
    protected $isError = false;
    protected $errorMessage = "";
    protected $response;


    public function __construct($data, $config)
    {
        $this->data = $this->mapData($data);
        $this->config = $config;
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


    protected function mapData($data)
    {
        return $data;
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
