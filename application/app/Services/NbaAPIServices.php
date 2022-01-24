<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class NbaAPIServices
{
    protected $url;
    protected $path;
    protected $page;
    protected $request;

    public function __construct()
    {
        $this->url = $_ENV['NBA_API_URL'];
    }

    public function setUrl($url)
    {
        $this->path = $url;
        return $this;
    }

    public function setPage($page){
        $this->page = '?page='.$page;
        return $this;
    }

    public function getRequest()
    {
        $requestUrl = $this->url.$this->path;
        if ($this->page){ $requestUrl .= $this->page; }

        $this->request = Http::get($requestUrl);
        return $this;
    }

    public function getResponse()
    {
        $result = $this->request->body();
        return json_decode($result, true);
    }
}
