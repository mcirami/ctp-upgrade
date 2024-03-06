<?php namespace LeadMax\TrackYourStats\Offer;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 9/15/2017
 * Time: 3:52 PM
 */

use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;


class Caps
{


    /* DB INFO

    offer_idoffer - offer id
    type - type of cap, clicks or conversion
    time_interval - time of max caps per defined time. i.e. daily, weekly, monthly
    interval_cap - # of clicks or conversions per time peroid
    redirect_offer - redirect offer





     */

    //  type enums, (for db)
    // e.g., if an offers cap is per click, in db, type = 0
    const clicks = 0;
    const conversions = 1;

    // time_interval enums
    const daily = 0;
    const weekly = 1;
    const monthly = 2;
    const total = 3;


    public $offerID = -1;

    public $type;
    public $time_interval;
    public $interval_cap;
    public $redirect_offer;
    public $status;

    public $cap_rules;


    private $updating = false;

    public function __construct($offerID, $updating = false)
    {
        $this->offerID = $offerID;
        $this->updating = $updating;
        $this->getOfferCapRules();
    }


    public static function duplicateCapRules($offer_id, $new_offer_id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM offer_caps WHERE offer_idoffer = :offer_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":offer_id", $offer_id);
        $prep->execute();

        if ($prep->rowCount() > 0) {
            $capRule = $prep->fetch(PDO::FETCH_OBJ);
            $sql = "INSERT INTO offer_caps (offer_idoffer, type, time_interval, interval_cap, redirect_offer, status) VALUES(:new_offer_id, :type, :time_interval,  :interval_cap, :redirect_offer, :status)";
            $prep = $db->prepare($sql);
            $prep->bindParam(":new_offer_id", $new_offer_id);
            $prep->bindParam(":type", $capRule->type);
            $prep->bindParam(":time_interval", $capRule->time_interval);
            $prep->bindParam(":interval_cap", $capRule->interval_cap);
            $prep->bindParam(":redirect_offer", $capRule->redirect_offer);
            $prep->bindParam(":status", $capRule->status);

            return $prep->execute();
        }

    }


    public function createCapRules($options)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO offer_caps (offer_idoffer,type, time_interval, interval_cap, redirect_offer)
            VALUES(:offerID, :type, :time_interval, :interval_cap,  :redirect_offer)";
        $prep = $db->prepare($sql);

        $prep->bindParam(":type", $options["type"]);
        $prep->bindParam(":time_interval", $options["time_interval"]);
        $prep->bindParam(":interval_cap", $options["interval_cap"]);
        $prep->bindParam(":redirect_offer", $options["redirect_offer"]);
        $prep->bindParam(":offerID", $this->offerID);

        if ($prep->execute()) {
            return true;
        }

        return false;
    }

    public function offerHasCap()
    {
        if ($this->cap_rules == false) {
            return "false";
        } else {
            if ($this->cap_rules["status"] == 1) {
                return "true";
            } else {
                return "false";
            }
        }

    }

    public function disableCap()
    {
        if ($this->cap_rules == false) {
            return false;
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE offer_caps SET status = 0, max_cap_status = 0 WHERE offer_idoffer = :offerID";
        $prep = $db->prepare($sql);

        $prep->bindParam(":offerID", $this->offerID);

        if ($prep->execute()) {
            return true;
        }

        return false;

    }

    public function updateOfferRules($options)
    {
        //if there wasnt already cap rules with an offer, create it
        if (!$this->cap_rules) {
            return $this->createCapRules($options);
        }


        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE offer_caps SET type = :type, time_interval = :time_interval, interval_cap = :interval_cap, redirect_offer = :redirect_offer, max_cap = :max_cap, max_cap_status = :max_cap_status, max_cap_date = :max_cap_date, status = 1 WHERE offer_idoffer = :offerID";
        $prep = $db->prepare($sql);

        $prep->bindParam(":type", $options["type"]);
        $prep->bindParam(":time_interval", $options["time_interval"]);
        $prep->bindParam(":interval_cap", $options["interval_cap"]);
        $prep->bindParam(":redirect_offer", $options["redirect_offer"]);
	    $prep->bindParam(":max_cap", $options["max_cap"]);
	    $prep->bindParam(":max_cap_status", $options["max_cap_status"]);
	    $prep->bindParam(":max_cap_date", $options["max_cap_date"]);
        $prep->bindParam(":offerID", $this->offerID);


        if ($prep->execute()) {
            return true;
        }

        return false;


    }

    public function getRuleVal($ruleName)
    {
        if ($this->cap_rules != false) {
            return $this->cap_rules[$ruleName];
        }
    }

    public function sendToRedirectOffer()
    {
        $url = findProtocol().$_SERVER["HTTP_HOST"];
        $url .= "/?";

        foreach ($_GET as $name => $val) {
            if ($name !== "offerid") {
                $url .= $name."=".$val."&";
            }
        }

        $url .= "offerid=".$this->cap_rules["redirect_offer"];

        send_to($url);

    }

    private function getOfferCapRules()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT * FROM offer_caps WHERE offer_idoffer = :offerID ";
        if ($this->updating == false) {
            $sql .= " AND status = 1 ";
        }
        $prep = $db->prepare($sql);

        $prep->bindParam(":offerID", $this->offerID);

        if ($prep->execute()) {
            $this->cap_rules = $prep->fetch(PDO::FETCH_ASSOC);
        } else {
            $this->cap_rules = false;

            return false;
        }


    }

    private function calculateTimeInterval()
    {


        if ($this->cap_rules["time_interval"] !== self::total) {
            switch ($this->cap_rules["type"]) {

                case self::clicks:
                    $query = " AND clicks.first_timestamp >= :dateFrom AND clicks.first_timestamp <= :dateTo ";
                    break;


                case self::conversions:
                    $query = " AND conversions.timestamp >= :dateFrom and conversions.timestamp <= :dateTo ";
                    break;

            }
        }


        switch ($this->cap_rules["time_interval"]) {
            case self::total:
                return ['dateFrom' => null, 'dateTo' => null, 'query' => ''];

            case self::daily:
	            $tz = 'America/New_York';
	            $timeNow = \Illuminate\Support\Carbon::today($tz)->format('Y-m-d');
	            $from = $timeNow . " 00:00:00";
	            $to = $timeNow . " 23:59:59";

	            $carbonFrom = Carbon::createFromFormat('Y-m-d H:i:s', $from, $tz);
	            $carbonTo = Carbon::createFromFormat('Y-m-d H:i:s', $to, $tz);
	            $dateFrom = $carbonFrom->setTimezone("UTC");
	            $dateTo = $carbonTo->setTimezone("UTC");

                return ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'query' => $query];

            case self::weekly:
                $date = new \DateTime(date("Y-m-d"));
                $daysToSubtract = date("N");
                $daysToSubtract--; //subtract one so it hits monday
                $date->sub(new \DateInterval('P'.$daysToSubtract.'D'));

                $dateFrom = $date->format("Y-m-d")." 00:00:00";
                $dateTo = date("Y-m-d"." 23:59:59");

                return ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'query' => $query];

            case self::monthly:
                $dateFrom = date("Y-m-01"." 00:00:00");
                $dateTo = date("Y-");
                $dateTo .= (date("m") + 1)."-31 23:59:59";

                return ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'query' => $query];

        }
    }


    public function isOfferCapped()
    {
        if ($this->cap_rules == false) {
            return false;
        }

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        switch ($this->cap_rules["type"]) {

            case self::clicks:
                $sql = "SELECT idclicks FROM clicks ";

                $sql .= " WHERE offer_idoffer = :offerID ";

                break;

            case self::conversions:
                $sql = "SELECT idclicks FROM clicks INNER JOIN conversions ON conversions.click_id = clicks.idclicks  ";

                $sql .= " WHERE offer_idoffer = :offerID ";
                break;

            default:
                return false;
        }


        $calculated = $this->calculateTimeInterval();
        $sql .= $calculated["query"];

        $prep = $db->prepare($sql);

        $prep->bindParam(":offerID", $this->offerID);

        if ($this->cap_rules["time_interval"] !== self::total) {
            $prep->bindParam(":dateFrom", $calculated["dateFrom"]);
            $prep->bindParam(":dateTo", $calculated["dateTo"]);
        }

        if ($prep->execute()) {

            if ($prep->rowCount() !== 0) {
                if ($prep->rowCount() >= $this->cap_rules["interval_cap"]) {

	                if (!$this->cap_rules["max_cap_status"]) {
		                return true;
	                }

					if ($this->cap_rules["max_cap_status"] && $this->cap_rules['max_cap_date']) {
						$tz = 'America/New_York';
						$dateToday = \Illuminate\Support\Carbon::today($tz)->startOfDay()->format('Y-m-d H:i:s');
						$carbonToday = Carbon::createFromFormat('Y-m-d H:i:s', $dateToday, $tz);
						$dateNow = $carbonToday->setTimezone("UTC");

						$clicksLog = new Logger('clicks');
						$clicksLog->pushHandler(new StreamHandler(storage_path('logs/clicks.log')), Logger::INFO);
						$log = [
							'now'           => $dateNow,
							'max_cap_date'  => $this->cap_rules['max_cap_date'],
							'count'         => $prep->rowCount(),
							'offerID'       => $this->offerID
						];
						$clicksLog->info('Click', $log);

						if($dateNow > $this->cap_rules['max_cap_date']) {
							$db   = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
							$sql  = "UPDATE offer_caps SET max_cap_status = 0, max_cap_date = NULL WHERE offer_idoffer = :offerID";
							$prep = $db->prepare( $sql );
							$prep->bindParam(":offerID", $this->offerID);
							$prep->execute();
							return false;
						} else if ($prep->rowCount() >= $this->cap_rules["max_cap"]) {
							return true;
						}
					}
                }
            }
        }

        return false;
    }


}