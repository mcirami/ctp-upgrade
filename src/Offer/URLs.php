<?php

namespace LeadMax\TrackYourStats\Offer;

use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\System\Company;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/25/2017
 * Time: 4:11 PM
 */
class URLs
{

    public $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }


    public function getOfferUrls()
    {
        $db = DatabaseConnection::getMasterInstance();
	    $sql = "SELECT offer_urls.id, offer_urls.url, offer_urls.status, offer_urls.company_id, offer_urls.assigned_manager_id, offer_urls.timestamp, rep.user_name AS manager_username FROM offer_urls LEFT JOIN rep ON rep.idrep = offer_urls.assigned_manager_id WHERE company_id = :company_id";
        $prep = $db->prepare($sql);
        $subDomain = $this->company->getID();
        $prep->bindParam(":company_id", $subDomain);
        $prep->execute();

        return $prep;
    }


    public function createOfferURL($offer_url, $status, $assignedManagerId = null)
    {
        $db = DatabaseConnection::getMasterInstance();
	    $sql = "INSERT INTO offer_urls (url, status, company_id, assigned_manager_id, timestamp) VALUES(:url, :status, :company_id, :assigned_manager_id, :timestamp)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":url", $offer_url);
        $prep->bindParam(":status", $status);
        $id = $this->company->getID();
        $prep->bindParam(":company_id", $id);
	    if ($assignedManagerId === null) {
		    $prep->bindValue(":assigned_manager_id", null, \PDO::PARAM_NULL);
	    } else {
		    $prep->bindValue(":assigned_manager_id", $assignedManagerId, \PDO::PARAM_INT);
	    }
        $date = date("Y-m-d H:i:s");
        $prep->bindParam(":timestamp", $date);

        return $prep->execute();
    }


    public function selectOne($offer_url_id, $company_id)
    {
        $db = DatabaseConnection::getMasterInstance();
	    $sql = "SELECT offer_urls.id, offer_urls.url, offer_urls.status, offer_urls.company_id, offer_urls.assigned_manager_id, offer_urls.timestamp FROM offer_urls LEFT JOIN company ON company.id = :company_id WHERE offer_urls.id = :offer_url_id";
        $prep = $db->prepare($sql);
        $prep->bindParam(":company_id", $company_id);
        $prep->bindParam(":offer_url_id", $offer_url_id);
        $prep->execute();

        return $prep;
    }

    public function updateOfferUrl($offer_url_id, $status, $url, $assignedManagerId = null)
    {
        $db = DatabaseConnection::getMasterInstance();
	    $sql = "UPDATE offer_urls SET status = :status, url = :url, assigned_manager_id = :assigned_manager_id WHERE id = :id AND company_id = :company_id";

        $prep = $db->prepare($sql);
        $prep->bindParam(":status", $status);
        $prep->bindParam(":url", $url);
        $prep->bindParam(":id", $offer_url_id);
	    if ($assignedManagerId === null) {
		    $prep->bindValue(":assigned_manager_id", null, \PDO::PARAM_NULL);
	    } else {
		    $prep->bindValue(":assigned_manager_id", $assignedManagerId, \PDO::PARAM_INT);
	    }

        $id = $this->company->getID();
        $prep->bindParam(":company_id", $id);

        return $prep->execute();
    }


}