<?php
namespace AppBundle\Correlator;
use DateTime;
use Exception;

/**
 * Class Pearson
 * @package AppBundle\Correlator
 */
class Pearson implements CorrelatorInterface
{
    /**
     * Should return a numeric representation of correlation between two array data sets
     *
     * @param array $dataSet1
     * @param array $dataSet2
     * @return int
     */
    public function getCorrelation($dataSet1, $dataSet2)
    {
        $groupedData = $this->groupData($dataSet1, $dataSet2);
        return $this->pearson($groupedData[0], $groupedData[1]);
    }

    /**
     * @param $dataSet1
     * @param $dataSet2
     * @return float|int
     * @throws Exception
     */
    public function pearson($dataSet1, $dataSet2)
    {
        if (count($dataSet1) === 0 || count($dataSet2) === 0)
        {
            return 0;
        }
        $pearson = 0;
        if (count($dataSet1) !== count($dataSet2)) {
            throw new Exception('We do not support arrays of varying lengths when finding pearson coefficient.');
        } else {
            $sumOf1 = 0;
            $sumOfSquare1 = 0;
            $sumOf2 = 0;
            $sumOfSquare2 = 0;
            $sumOfProducts = 0;

            foreach ($dataSet1 as $i => $data1) {
                $data2 = $dataSet2[$i];
                $sumOf1 += $data1;
                $sumOfSquare1 += pow($data1, 2);
                $sumOf2 += $data2;
                $sumOfSquare2 += pow($data2, 2);
                $sumOfProducts += $data1 * $data2;
            }

            // calculate pearson
            $numerator = $sumOfProducts - ($sumOf1 * $sumOf2 / count($dataSet1));
            $denominator = sqrt(
                ($sumOfSquare1 - pow($sumOf1, 2) / count($dataSet1))
                * ($sumOfSquare2 - pow($sumOf2, 2) / count($dataSet1))
            );
            if ($denominator == 0) {
                $pearson = 0;
            }
            else {
                $pearson = $numerator / $denominator;
            }
        }

        return $pearson;
    }

    /**
     * Should return an array of two arrays of plotted data grouped by date (with same dates in each array as indexes)
     *
     * [
     *  '2015-05-25' => 10.5,
     *  '2015-05-26' => 10.3
     * ]
     *
     * @return array
     */
    public function groupData($dataSet1, $dataSet2)
    {
        $dataSet1Grouped = $this->groupDataByDate($dataSet1);
        $dataSet2Grouped = $this->groupDataByDate($dataSet2);
        $dataSet1Intersection = array_intersect_key($dataSet1Grouped, $dataSet2Grouped);
        $dataSet2Intersection = array_intersect_key($dataSet2Grouped, $dataSet1Intersection);

        return [$dataSet1Intersection, $dataSet2Intersection];
    }

    /**
     * @param $dataSet
     * @return array
     */
    public function groupDataByDate($dataSet)
    {
        $dataSetGroupedByDate = [];
        $groupDateCount = [];
        foreach ($dataSet as $data) {
            /** @var DateTime $timestamp */
            $timestamp = $data['timestamp'];
            $key = $timestamp->format('Y-m-d');
            if (!array_key_exists($key, $dataSetGroupedByDate)) {
                $dataSetGroupedByDate[$key] = $data['units'];
                $groupDateCount[$key] = 1;
            } else {
                $dataSetGroupedByDate[$key] += $data['units'];
                $groupDateCount[$key] += 1;
            }
        }

        // Find the mean of the data for a given day
        array_walk($dataSetGroupedByDate, function (&$item, $index) use ($groupDateCount) {
            $item = $item / $groupDateCount[$index];
        });

        return $dataSetGroupedByDate;
    }
}