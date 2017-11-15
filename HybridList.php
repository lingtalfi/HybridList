<?php


namespace HybridList;


use HybridList\ListShaper\ListShaperInterface;
use HybridList\RequestGenerator\RequestGeneratorInterface;
use HybridList\RequestGenerator\SqlRequestGenerator;
use HybridList\RequestShaper\RequestShaperInterface;
use HybridList\Shaper\ShaperInterface;


/**
 *
 * With
 *
 *
 */
class HybridList implements HybridListInterface
{

    /**
     * @var RequestGeneratorInterface
     */
    private $requestGenerator;
    /**
     * @var string, the key through which we access the sort identifier (in list parameters)
     */
    private $sortKey;
    private $listParameters;
    private $listShapers;
    private static $allowedListInfoOverride = [
        'sliceNumber',
        'sliceLength',
        'totalNumberOfItems',
        'offset',
    ];

    public function __construct()
    {
        $this->requestGenerator = null;
        $this->sortKey = "sort";
        $this->listParameters = [];
        $this->listShapers = [];
    }

    public static function create()
    {
        return new static();
    }


    public function execute()
    {
        $listInfo = [
            'items' => [],
            'sliceNumber' => null,
            'sliceLength' => null,
            'totalNumberOfItems' => 0,
            'offset' => null,
        ];


        //--------------------------------------------
        // TREAT SQL PART
        //--------------------------------------------
        if ($this->requestGenerator instanceof SqlRequestGenerator) {
            $sqlRequest = $this->requestGenerator->getSqlRequest();
            $params2Shapers = $this->getParam2Shapers($this->requestGenerator->getRequestShapers());

            //--------------------------------------------
            // NOW EXECUTE THE RELEVANT SHAPERS
            //--------------------------------------------
            foreach ($this->listParameters as $key => $value) {
                if (array_key_exists($key, $params2Shapers)) {
                    /**
                     * @var $shaper RequestShaperInterface
                     */
                    $shaper = $params2Shapers[$key];
                    $shaper->execute($value, $sqlRequest);
                }
            }
        }

        //--------------------------------------------
        // NOW GET THE BASE LIST
        //--------------------------------------------
        $items = $this->requestGenerator->getItems();


        //--------------------------------------------
        // COLLECT ALL THE INFO THAT WE CAN FROM THE SQL
        //--------------------------------------------
        if ($this->requestGenerator instanceof SqlRequestGenerator) {
            $info = $this->requestGenerator->getAfterItemsInfo();
            self::mergeInfo($info, $listInfo);
        }


        //--------------------------------------------
        // TREAT THE PHP PART
        //--------------------------------------------
        if ($items) {
            $items = $this->preparePhpItems($items);
            $originalItems = $items;
            if ($this->listShapers) {
                //--------------------------------------------
                // BUILD THE ARRAY OF PARAMETERS => SHAPERS TO EXECUTE
                //--------------------------------------------
                $params2Shapers = $this->getParam2Shapers($this->listShapers);


                //--------------------------------------------
                // NOW EXECUTE THE RELEVANT SHAPERS
                //--------------------------------------------
                foreach ($this->listParameters as $key => $value) {
                    if (array_key_exists($key, $params2Shapers)) {
                        /**
                         * @var $shaper ListShaperInterface
                         */
                        $shaper = $params2Shapers[$key];
                        $shaper->execute($value, $items, $listInfo, $originalItems);
                    }
                }

                //--------------------------------------------
                // WILDCARD SHAPER
                //--------------------------------------------
                if (array_key_exists("*", $params2Shapers)) {
                    foreach ($params2Shapers["*"] as $shaper) {
                        $shaper->execute("*", $items, $listInfo, $originalItems);
                    }
                }
            }


            //--------------------------------------------
            //
            //--------------------------------------------
            $listInfo = [
                'items' => $items,
                'sliceNumber' => $listInfo['sliceNumber'],
                'sliceLength' => $listInfo['sliceLength'],
                'totalNumberOfItems' => $listInfo['totalNumberOfItems'],
                'offset' => $listInfo['offset'],
            ];
        }
        return $listInfo;


    }


    //--------------------------------------------
    //
    //--------------------------------------------
    public function setRequestGenerator(RequestGeneratorInterface $requestGenerator)
    {
        $this->requestGenerator = $requestGenerator;
        return $this;
    }

    /**
     * @return RequestGeneratorInterface
     */
    public function getRequestGenerator()
    {
        return $this->requestGenerator;
    }


    public function addListShaper(ListShaperInterface $listShaper)
    {
        $this->listShapers[] = $listShaper;
        return $this;
    }

    public function setListParameters(array $listParameters)
    {
        $this->listParameters = $listParameters;
        return $this;
    }

    /**
     * @return array
     */
    public function getListParameters()
    {
        return $this->listParameters;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    protected function preparePhpItems(array $items)
    {
        return $items;
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    private function getParam2Shapers(array $shapers)
    {
        $params2Shapers = [];
        foreach ($shapers as $shaper) {
            /**
             * @var $shaper ShaperInterface
             */
            $params = $shaper->getReactsTo();
            foreach ($params as $param) {
                if ('*' === $param) {
                    $params2Shapers[$param][] = $shaper;
                } else {
                    $params2Shapers[$param] = $shaper;
                }
            }
        }
        return $params2Shapers;
    }

    private static function mergeInfo(array $info, array &$listInfo)
    {

        if ($info) {
            foreach ($info as $k => $v) {
                if (in_array($k, self::$allowedListInfoOverride)) {
                    $listInfo[$k] = $v;
                }
            }
        }
    }

}