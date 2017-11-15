<?php


namespace HybridList\ListShaper;


use HybridList\Shaper\Shaper;

class ListShaper extends Shaper implements ListShaperInterface
{
    public function execute($input, array &$rows, array &$info = [], array $originalItems = [])
    {
        if (null !== $this->executeCallback) {
            call_user_func_array($this->executeCallback, [$input, &$rows, &$info, $originalItems]);
        }
    }
}