<?php
namespace AppBundle\Correlator;


class SimpleSlope implements CorrelatorInterface {

    public function getCorrelation($dataSet1, $dataSet2)
    {
        $slope1 = $this->calculateSlopOfDataSet($dataSet1);
        $slope2 = $this->calculateSlopOfDataSet($dataSet2);

        if ($slope2 == 0) {
            return 0;
        }

        return $slope1 / $slope2;
    }

    public function calculateSlopOfDataSet($dataset)
    {
        $rise = $dataset[1]['units'] - $dataset[0]['units'];
        $run = $dataset[1]['timestamp']->getTimestamp() - $dataset[0]['timestamp']->getTimestamp();

        if ($run == 0) {
            return 0;
        }

        return $rise / $run;
    }
}