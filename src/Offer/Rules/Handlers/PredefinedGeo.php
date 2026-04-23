<?php

namespace LeadMax\TrackYourStats\Offer\Rules\Handlers;

use PDO;

class PredefinedGeo
{
    public static function printOptionsForUser(int $userID): void
    {
        foreach (self::getOptionsForUser($userID) as $option) {
            $name = htmlspecialchars($option["name"], ENT_QUOTES, "UTF-8");
            $id = (int) $option["id"];

            echo "<option value = \"{$id}\">{$name}</option>";
        }
    }

    public static function getOptionsForUser(int $userID): array
    {
        try {
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "SELECT id, name
                    FROM predefined_geo_rules
                    WHERE rep_idrep = :userID
                    ORDER BY name ASC";

            $prep = $db->prepare($sql);
            $prep->bindParam(":userID", $userID);
            $prep->execute();

            return $prep->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function findForUser(int $presetID, int $userID): ?array
    {
        try {
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

            $sql = "SELECT id, name, redirect_offer, deny, is_active
                    FROM predefined_geo_rules
                    WHERE id = :presetID
                        AND rep_idrep = :userID
                    LIMIT 1";

            $prep = $db->prepare($sql);
            $prep->bindParam(":presetID", $presetID);
            $prep->bindParam(":userID", $userID);
            $prep->execute();

            $preset = $prep->fetch(PDO::FETCH_ASSOC);

            if (!$preset) {
                return null;
            }

            $countrySql = "SELECT country_code, country_name, cap_status, cap
                           FROM predefined_geo_rule_countries
                           WHERE predefined_geo_rule_id = :presetID
                           ORDER BY country_name ASC, country_code ASC";

            $countryPrep = $db->prepare($countrySql);
            $countryPrep->bindParam(":presetID", $presetID);
            $countryPrep->execute();

            $preset["redirectOffer"] = $preset["redirect_offer"];
            unset($preset["redirect_offer"]);
            $preset["countries"] = $countryPrep->fetchAll(PDO::FETCH_ASSOC);

            return $preset;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function createFromGeoPostData(int $userID, string $name, array $geoPostData): int
    {
        $trimmedName = trim($name);

        if ($trimmedName === "") {
            throw new \InvalidArgumentException("Predefined rule name is required.");
        }

        $countries = self::parseCountries($geoPostData);

        if (count($countries) === 0) {
            throw new \InvalidArgumentException("At least one country is required to create a predefined rule.");
        }

        $redirectOffer = null;
        if (!empty($geoPostData[2])) {
            $redirectOffer = (int) $geoPostData[2];
        }

        $deny = !empty($geoPostData[3]) ? 1 : 0;
        $isActive = array_key_exists(4, $geoPostData)
            ? (filter_var($geoPostData[4], FILTER_VALIDATE_BOOLEAN) ? 1 : 0)
            : 1;

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $db->beginTransaction();

        try {
            $sql = "INSERT INTO predefined_geo_rules (rep_idrep, name, redirect_offer, deny, is_active, created_at, updated_at)
                    VALUES (:userID, :name, :redirectOffer, :deny, :isActive, NOW(), NOW())";

            $prep = $db->prepare($sql);
            $prep->bindParam(":userID", $userID);
            $prep->bindParam(":name", $trimmedName);
            $prep->bindParam(":redirectOffer", $redirectOffer);
            $prep->bindParam(":deny", $deny);
            $prep->bindParam(":isActive", $isActive);
            $prep->execute();

            $presetID = (int) $db->lastInsertId();

            $questionMarks = [];
            $values = [];

            foreach ($countries as $country) {
                $questionMarks[] = "(?,?,?,?,?,NOW(),NOW())";
                $values[] = $presetID;
                $values[] = $country["country_code"];
                $values[] = $country["country_name"];
                $values[] = $country["cap_status"];
                $values[] = $country["cap"];
            }

            $countrySql = "INSERT INTO predefined_geo_rule_countries (predefined_geo_rule_id, country_code, country_name, cap_status, cap, created_at, updated_at)
                           VALUES " . implode(",", $questionMarks);

            $countryPrep = $db->prepare($countrySql);
            $countryPrep->execute($values);

            $db->commit();

            return $presetID;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function createFromRuleDataAndCountryList(int $userID, string $name, $ruleData, array $countryList): int
    {
        $payload = [
            0,
            $ruleData->name ?? "",
            $ruleData->redirectOffer ?? null,
            $ruleData->deny ?? 0,
            $ruleData->is_active ?? 1,
        ];

        foreach ($countryList as $country) {
            if (is_object($country)) {
                $country = (array) $country;
            }

            if (is_array($country)) {
                $payload[] = $country;
            }
        }

        return self::createFromGeoPostData($userID, $name, $payload);
    }

    private static function parseCountries(array $geoPostData): array
    {
        $countries = [];

        foreach ($geoPostData as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (!is_array($item) || count($item) < 4) {
                continue;
            }

            $countries[] = [
                "country_code" => (string) $item[0],
                "country_name" => (string) $item[1],
                "cap_status" => filter_var($item[2], FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                "cap" => (int) $item[3],
            ];
        }

        return $countries;
    }
}
