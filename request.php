<?php

class SimpleJsonRequest
{

    private static $objMemcache;

    //RETORNA A INSTANCIA DO CACHE
    protected static function getMemcache()
    {
        if(!isset(self::$objMemcache)) {
            self::$objMemcache = new Memcache();
            self::$objMemcache->addServer('127.0.0.1', 11211);
        }
        return self::$objMemcache;
    }

    private static function makeRequest(string $method, string $url, array $parameters = null, array $data = null)
    {
        $opts = [
            'http' => [
                'method'  => $method,
                'header'  => 'Content-type: application/json',
                'content' => $data ? json_encode($data) : null
            ]
        ];

        $url .= ($parameters ? '?' . http_build_query($parameters) : '');

        $mc = self::getMemcache();

        //os endpoints são guardados como chaves
        if ($mc->get($url)) {
            $result = $mc->get($url);
        } else {
            $result = file_get_contents($url, false, stream_context_create($opts));
            //armazena durante 120s
            $mc->set($url, $result, false, 120);
        }

        return json_encode($result);
    }

    public static function get(string $url, array $parameters = null)
    {
        return json_decode(self::makeRequest('GET', $url, $parameters));
    }

    public static function post(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('POST', $url, $parameters, $data));
    }

    public static function put(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PUT', $url, $parameters, $data));
    }   

    public static function patch(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PATCH', $url, $parameters, $data));
    }

    public static function delete(string $url, array $parameters = null, array $data = null)
    {
        return json_decode(self::makeRequest('DELETE', $url, $parameters, $data));
    }

}

echo SimpleJsonRequest::get("your_url", []);
