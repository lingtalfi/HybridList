<?php


namespace HybridList\RequestGenerator;


use HybridList\RequestShaper\RequestShaperInterface;

class RequestGenerator implements RequestGeneratorInterface
{

    protected $requestShapers;
    protected $items;

    public function __construct()
    {
        $this->requestShapers = [];
        $this->items = [];
    }


    public static function create()
    {
        return new static();
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    public function getRequestShapers()
    {
        return $this->requestShapers;
    }

    public function addRequestShaper(RequestShaperInterface $shaper)
    {
        $this->requestShapers[] = $shaper;
        return $this;
    }
}