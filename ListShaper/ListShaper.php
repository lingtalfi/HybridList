<?php


namespace HybridList\ListShaper;


use HybridList\Shaper\Shaper;

class ListShaper extends Shaper implements ListShaperInterface
{
    private $prepareCallback;


    public function execute($input, array &$rows, array &$info = [], array $originalItems = [])
    {
        if (null !== $this->executeCallback) {
            call_user_func_array($this->executeCallback, [$input, &$rows, &$info, $originalItems]);
        }
    }

    public function prepareWithOriginalItems(array $originalItems)
    {
        if (null !== $this->prepareCallback) {
            call_user_func($this->prepareCallback, $originalItems);
        }
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    public function setPrepareCallback(callable $cb)
    {
        $this->prepareCallback = $cb;
        return $this;
    }

}