<?php
namespace AppBundle\Correlator;


class SimpleSlope implements CorrelatorInterface {

    public function getCorrelation($dataSet1, $dataSet2)
    {
        $slope1 = $this->calculateSlopOfDataSet($dataSet1);
        $slope2 = $this->calculateSlopOfDataSet($dataSet2);

        return $slope1 / $slope2;
    }

    protected function calculateSlopOfDataSet($dataset)
    {
        $rise = $dataset[1]['units'] - $dataset[0]['units'];
        $run = $dataset[1]['timestamp'] - $dataset[0]['timestamp'];

        return $rise / $run;
    }
}