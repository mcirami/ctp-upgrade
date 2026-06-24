<?php

namespace LeadMax\TrackYourStats\Clicks\URLEvents;

use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use PDO;
use RuntimeException;
use Throwable;

class ValueRoutedConversionRegistrationEvent extends URLEvent
{
    private float $postbackValue;

    private int $targetOfferId;

    public function __construct(int $sourceClickId, float $postbackValue, int $targetOfferId)
    {
        $this->clickId = $sourceClickId;
        $this->postbackValue = $postbackValue;
        $this->targetOfferId = $targetOfferId;
    }

    public static function getEventString(): string
    {
        return 'value_routed_convert';
    }

    public function fire()
    {
        $db = DatabaseConnection::getInstance();

        try {
            $db->beginTransaction();

            $sourceClick = $this->findSourceClick($db);
            $this->validateTargetOffer($db, (int) $sourceClick->rep_idrep);
            $route = $this->lockOrCreateRoute($db);

            if ($route->completed_at !== null) {
                $db->commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Generated conversion was already registered.',
                    'generated_click_id' => (int) $route->generated_click_id,
                ]);
            }

            $generatedClickId = $route->generated_click_id
                ? (int) $route->generated_click_id
                : $this->createGeneratedClick($sourceClick);

            if (!$route->generated_click_id) {
                $this->saveGeneratedClickId($db, (int) $route->id, $generatedClickId);
            }

            if (Conversion::isClickConverted($generatedClickId)) {
                $this->markCompleted($db, (int) $route->id);
                $db->commit();

                return response()->json([
                    'status' => 200,
                    'message' => 'Generated conversion was already registered.',
                    'generated_click_id' => $generatedClickId,
                ]);
            }

            $response = (new ConversionRegistrationEvent($generatedClickId))->fire();

            if ($response->getStatusCode() >= 400) {
                throw new RuntimeException($response->getContent());
            }

            $this->markCompleted($db, (int) $route->id);
            $db->commit();

            return response()->json([
                'status' => 200,
                'message' => 'Value-routed conversion registered.',
                'source_click_id' => $this->clickId,
                'generated_click_id' => $generatedClickId,
                'target_offer_id' => $this->targetOfferId,
            ]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function findSourceClick(PDO $db): object
    {
        $statement = $db->prepare(
            'SELECT clicks.*, click_vars.url, click_vars.sub1, click_vars.sub2,
                    click_vars.sub3, click_vars.sub4, click_vars.sub5
             FROM clicks
             LEFT JOIN click_vars ON click_vars.click_id = clicks.idclicks
             WHERE clicks.idclicks = :click_id
             LIMIT 1'
        );
        $statement->execute(['click_id' => $this->clickId]);
        $click = $statement->fetch(PDO::FETCH_OBJ);

        if (!$click) {
            throw new RuntimeException("Invalid source click ID: {$this->clickId}");
        }

        return $click;
    }

    private function validateTargetOffer(PDO $db, int $affiliateId): void
    {
        $statement = $db->prepare(
            'SELECT offer.idoffer
             FROM offer
             INNER JOIN rep_has_offer
                ON rep_has_offer.offer_idoffer = offer.idoffer
               AND rep_has_offer.rep_idrep = :affiliate_id
             WHERE offer.idoffer = :offer_id
               AND offer.status = 1
             LIMIT 1'
        );
        $statement->execute([
            'affiliate_id' => $affiliateId,
            'offer_id' => $this->targetOfferId,
        ]);

        if (!$statement->fetchColumn()) {
            throw new RuntimeException(
                "Target offer {$this->targetOfferId} is inactive or is not assigned to affiliate {$affiliateId}."
            );
        }
    }

    private function lockOrCreateRoute(PDO $db): object
    {
        $statement = $db->prepare(
            'INSERT IGNORE INTO postback_value_sales
                (source_click_id, target_offer_id, postback_value, created_at, updated_at)
             VALUES (:source_click_id, :target_offer_id, :postback_value, NOW(), NOW())'
        );
        $statement->execute([
            'source_click_id' => $this->clickId,
            'target_offer_id' => $this->targetOfferId,
            'postback_value' => $this->postbackValue,
        ]);

        $statement = $db->prepare(
            'SELECT *
             FROM postback_value_sales
             WHERE source_click_id = :source_click_id
               AND target_offer_id = :target_offer_id
             FOR UPDATE'
        );
        $statement->execute([
            'source_click_id' => $this->clickId,
            'target_offer_id' => $this->targetOfferId,
        ]);
        $route = $statement->fetch(PDO::FETCH_OBJ);

        if (!$route) {
            throw new RuntimeException('Unable to reserve the generated conversion.');
        }

        return $route;
    }

    private function createGeneratedClick(object $sourceClick): int
    {
        $click = new Click();
        $click->rep_idrep = (int) $sourceClick->rep_idrep;
        $click->offer_idoffer = $this->targetOfferId;
        $click->first_timestamp = date('Y-m-d H:i:s');
        $click->ip_address = $sourceClick->ip_address;
        $click->country_code = $sourceClick->country_code;
        $click->referer = $sourceClick->referer;
        $click->browser_agent = 'TYS_GENERATED_VALUE_POSTBACK';
        $click->click_type = Click::TYPE_GENERATED;
        $click->subVarArray = [
            'sub1' => $sourceClick->sub1 ?? '',
            'sub2' => $sourceClick->sub2 ?? '',
            'sub3' => $sourceClick->sub3 ?? '',
            'sub4' => $sourceClick->sub4 ?? '',
            'sub5' => $sourceClick->sub5 ?? '',
        ];
        $click->queryString = $sourceClick->url
            ?: "/?generated_from_click={$this->clickId}&offerid={$this->targetOfferId}";

        if (!$click->save()) {
            throw new RuntimeException('Unable to create the generated click.');
        }

        return (int) $click->id;
    }

    private function saveGeneratedClickId(PDO $db, int $routeId, int $generatedClickId): void
    {
        $statement = $db->prepare(
            'UPDATE postback_value_sales
             SET generated_click_id = :generated_click_id, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'generated_click_id' => $generatedClickId,
            'id' => $routeId,
        ]);
    }

    private function markCompleted(PDO $db, int $routeId): void
    {
        $statement = $db->prepare(
            'UPDATE postback_value_sales
             SET completed_at = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $routeId]);
    }
}
