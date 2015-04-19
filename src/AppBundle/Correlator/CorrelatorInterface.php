<?php
namespace AppBundle\Correlator;

interface CorrelatorInterface {
    /**
     * Should return a numeric representation of correlation between two array data sets
     *
     * @param array $dataSet1
     * @param array $dataSet2
     * @return int
     */
    public function getCorrelation($dataSet1, $dataSet2);
}