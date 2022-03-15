<?php
namespace Helpers;

/**
 * Class B24
 * Класс для работы с запросами REST Битрикс24.
 *
 * @package SL
 */
class B24
{
    /**
     * Адрес Битрикс24
     *
     * @var string
     */
    private $url;

    /**
     * Код доступа для REST-запросов к Битрикс24
     *
     * @var string
     */
    private $token;


    /**
     *
     * @return mixed
     */
    public function __construct($arParams) {
        if ($arParams['B24_URL']) {
            $this->url = $arParams['B24_URL'];
        } else {
            if (defined('B24_URL')) {
                $this->url = B24_URL;
            } else {

                return false;
            }
        }
        if ($arParams['B24_TOKEN']) {
            $this->token = $arParams['B24_TOKEN'];
        } else {
            if (defined('B24_TOKEN')) {
                $this->token = B24_TOKEN;
            } else {

                return false;
            }
        }
    }

    public function send($method, $arParams) {
        $queryUrl = 'https://'.$this->url.'/rest/1/'.$this->token.'/'.$method.'.json';
        $queryData = http_build_query($arParams);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ));

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true)['result'];
    }

}

