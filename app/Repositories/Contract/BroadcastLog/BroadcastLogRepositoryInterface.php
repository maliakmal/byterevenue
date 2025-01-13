<?php

namespace App\Repositories\Contract\BroadcastLog;

use App\Repositories\Contract\BaseRepositoryInterface;

interface BroadcastLogRepositoryInterface extends BaseRepositoryInterface
{
    public function updateWithIDs(array $ids, $fieldsToUpdate);

    public function updateBySlug($slug, $fieldsToUpdate);

    public function paginateBroadcastLogs(array $inputs);

    public function getUnsent();

    public function getUnsentCount();

    public function getUnsentCountByUserIds(array $userIds);

    public function getUnsentCountByCampaignIds(array $campaignIds);

    public function getUniqueCampaignsIDs(?int $limit = null, ?array $ignored_campaigns = null);

    public function getUniqueCampaignsIDsFromExistingBatch($batch);

    public function getTotalSentAndClicksByCampaign($campaign_id);

    public function getSentAndClicksByCampaign($campaign_id);

    public function getTotalSentAndClicksByBatch($batch);

    public function getTotalSentAndClicksByCampaignAndBatch($campaign_id, $batch_no);

    public function getClickedCount();

    public function getArchivedClickedCount();

    public function getSendCount();

    public function getArchivedSendCount();

    public function getTotalCount();

    public function getArchivedTotalCount();

    public function getSendCountByUserIds(array $userIds);

    public function getArchivedSendCountByUserIds(array $userIds);

    public function getArchivedSendCountByCampaignIds(array $campaignIds);

    public function getClickedCountByUserIds(array $userIds);

    public function getArchivedClickedCountByUserIds(array $userIds);

    public function getArchivedClickedCountByCampaignIds(array $campaignIds);
}
