<?php


namespace HybridList;


use HybridList\ListShaper\ListShaperInterface;
use HybridList\RequestGenerator\RequestGeneratorInterface;

interface HybridListInterface
{

    /**
     * @return array
     *          - items: the rows
     *          - sliceNumber: the number of the slice representing the items (aka the current page number)
     *          - sliceLength: the number of items per slice
     *          - totalNumberOfItems: the total number of items
     *          - offset: the offset of the returned slice's first element (compared to the whole items array)
     *
     */
    public function execute();


    public function setRequestGenerator(RequestGeneratorInterface $requestGenerator);

    /**
     * @return RequestGeneratorInterface
     */
    public function getRequestGenerator();


    public function addListShaper(ListShaperInterface $listShaper);

    /**
     * The entry point of the hybrid list, this is where you inject your $_GET/$_POST/testArray
     * parameters...
     */
    public function setListParameters(array $listParameters);

}