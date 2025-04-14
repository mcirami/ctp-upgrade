<?php namespace LeadMax\TrackYourStats\Report\Filters;


class EarningPerClick implements Filter
{

    /** Array key for UniqueClicks
     * @var string
     */
    public $clicksArrayKey;

    /** Array key for Revenue
     * @var string
     */
    public $revenueArrayKey;


    public function __construct($clicksArrayKey = "UniqueClicks", $revenueArrayKey = "Revenue")
    {
        $this->clicksArrayKey = $clicksArrayKey;
        $this->revenueArrayKey = $revenueArrayKey;
    }



    // Isn't there a math function for this?

    /** Rounds $val to the nearest 100th place decimal
     * @param $val
     * @return float
     */
    public function roundEPC($val)
    {
        if (($p = strpos($val, '.')) !== false) {
            if (floatval(substr($val, 4, $p)) >= 5) {
                /*$val += 0.01;
                $val = $this->truncate($val, 2);*/

	            $rounded = round($val, 4);
	            $val = number_format($rounded, 4, '.', '');
            } else {
	            $rounded = round($val, 4);
	            $val = number_format($rounded, 4, '.', '');
                //$val = $this->truncate($val, 2);
            }
        }

        return $val;
    }


    /** Truncate float to double.
     * @param $val
     * @param string $f
     * @return float
     */
    public function truncate($val, $f = "0")
    {
        if (($p = strpos($val, '.')) !== false) {
            $val = floatval(substr($val, 0, $p + 1 + $f));
        }

        return $val;
    }


    /** Filters the given report.
     * @param $report
     * @return mixed
     */
    public function filter($report)
    {
        $clicks = $this->clicksArrayKey;
        $revenue = $this->revenueArrayKey;


        foreach ($report as $row => $key) {


            if (isset($key[$clicks]) && isset($key[$revenue])) {
                if ($key[$clicks] != 0 && $key[$revenue] !== null) {
	                $rounded = round((float) $report[$row][$revenue], 4);
	                $report [$row]["EPC"]  = number_format($rounded, 4, '.', '') / $key[$clicks];
                    //$report [$row]["EPC"] = $this->roundEPC(($revenue / $key[$clicks]));
                } else {
                    $report [$row]["EPC"] = 0;
                }
            }


        }

        return $report;
    }

}