<?php

namespace App\Services\Campaign;

use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Model\Campaign\CampaignRepository;
use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Campaign\CreateCampaignRequest;
use App\Services\Keitaro\Requests\Campaign\GetAllCampaignsRequest;
use App\Services\Keitaro\Requests\Campaign\MoveCampaignToArchiveRequest;
use App\Services\Keitaro\Requests\Flows\CreateFlowRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Hidehalo\Nanoid\Client;
use Hidehalo\Nanoid\GeneratorInterface;
use App\Jobs\ProcessCampaign;


class CampaignService
{
    private $campaignRepository;
    private $nanoid;

    /**
     * CampaignService constructor.
     * @param CampaignRepository $campaignRepository
     */
    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
        $this->nanoid = new Client();
    }

    public function generateUrlForCampaign($domain, $alias, $messageID = null)
    {
        $param = config('app.keitaro.uid_param', 'sub_id_1');
        return $domain.DIRECTORY_SEPARATOR.$alias.( $messageID ? '?'.$param.'='.$messageID : '' );
    }

    public function createCampaignOnKeitaro($alias, $title, $groupID, $domainID, $type = 'position', $uniqueness_method = 'ip_ua',
                                            $cookies_ttl = 24, $position = 9999, $state = 'active', $cost_type = 'CPC', $cost_value = 0,
                                            $cost_currency = 'USD', $traffic_source_id = 0, $cost_auto = true, $uniqueness_use_cookies = true,
                                            $traffic_loss = 0

    )
    {

        $keitaro_token = uniqid();
        $create_campaign_request = new CreateCampaignRequest($alias, $title, $keitaro_token, $type, $groupID.'',
            $domainID, $cookies_ttl, $state, $cost_type, $cost_value, $cost_currency, $cost_auto, null,
        $traffic_source_id,null,null,null, $uniqueness_method, $position, $uniqueness_use_cookies,
        $traffic_loss);
        return KeitaroCaller::call($create_campaign_request);
    }

    public function createFlowOnKeitaro($campaignID, $campaignTitle, $action_payload = null, $filters = null, $action_options = null,
                                        $type = 'forced', $schema = 'redirect', $position = 1,
                                        $comments = null, $state = 'active', $action_type = 'http',
                                        $collect_clicks = true, $filter_or = false, $weight = 100,
    )
    {
        $create_flow_request = new CreateFlowRequest(
            $campaignID, $schema, $type,
            Str::slug($campaignTitle), $action_type, $action_payload, $position, $weight, $action_options, $comments, $state,
            $collect_clicks, $filter_or, $filters
        );

        return KeitaroCaller::call($create_flow_request);
    }

    /**
     * @param int|null $limit
     * @param int $offset
     * @return mixed
     */
    public function getAllCampaigns(?int $limit, int $offset)
    {
        $request = new GetAllCampaignsRequest($limit, $offset);

        return KeitaroCaller::call($request);
    }

    /**
     * @param int $campaignID
     * @return mixed
     */
    public function moveCampaignToArchive(int $campaignID)
    {
        $request = new MoveCampaignToArchiveRequest($campaignID);

        return KeitaroCaller::call($request);
    }

    /**
     * @param array $filter
     *
     * @return LengthAwarePaginator
     */
    public function getCampaignsFiltered(array $filter)
    {
        return $this->campaignRepository->getFiltered($filter);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function store(array $data)
    {
        try {
            DB::beginTransaction();
            $campaign = auth()->user()->campaigns()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'recipients_list_id' => $data['recipients_list_id'],
            ]);
            $campaign->generateUniqueFolder();
            $campaign->save();
            if (auth()->user()->show_introductory_screen == true) {
                User::where('id', auth()->id())->update(['show_introductory_screen' => false]);
            }
            //
//                $create_group_request = new CreateGroupRequest($campaign->title, 'campaigns');
//                $response = KeitaroCaller::call($create_group_request);
//                $campaign->keitaro_group_id = $response['id'];
//                $campaign->keitaro_create_group_response = @json_encode($response);
//                $campaign->save();
            DB::commit();
        } catch (RequestException $exception) {
            DB::rollBack();

            return [null, ['message' => $exception->getMessage()]];
        } catch (\Exception $exception) {
            DB::rollBack();
            report($exception);

            return [null, ['message' => 'Error Create Campaign']];
        }

        $message_data = [
            'subject' => $data['message_subject'],
            'body' => $data['message_body'],
            'target_url' => $data['message_target_url'],
            "user_id" => auth()->id(),
            'campaign_id' => $campaign->id
        ];
        Message::create($message_data);

        return [$campaign, null];
    }

    /**
     * @param int $id
     * @param array $filters
     *
     * @return array
     */
    public function show(int $id, array $filters)
    {
        $per_page = 5;
        $campaign = $this->campaignRepository->find($id);
        $message = $campaign->message;

        $recipient_lists = $campaign->recipient_list;

        if ($campaign->isDraft()) {
            $contacts = $recipient_lists->contacts();

            if (isset($filters['search'])) {
                $contacts = $contacts->where('recipient_phone', 'like', '%' . $filters['search'] . '%');
            }

            $contacts = $contacts->paginate($filters['per_page'] ?? $per_page);
            $logs = [];

        } else {
            $contacts = [];
            $logs = BroadcastLog::where('campaign_id', $campaign->id);

            if (isset($filters['sort'])) {
                switch ($filters['sort']) {
                    case 'blocked':
                        $logs = $logs->withIsBlocked()->orderBy('is_blocked', $filters['sort_order']);
                        break;
                    case 'status':
                        $logs = $logs->orderBy('status', $filters['sort_order']);
                        break;
                    case 'clicked':
                        $logs = $logs->orderBy('is_click', $filters['sort_order']);
                        break;
                    default:
                        $logs = $logs->orderBy('created_at', $filters['sort_order']);
                        break;
                }
            }

            if (isset($filters['search'])) {
                $logs = $logs->where('recipient_phone', 'like', '%' . $filters['search'] . '%');
            }

            $logs = $logs->paginate($filters['per_page'] ?? $per_page);
        }

        return [
            'campaign' => $campaign,
            'message' => $message,
            'contacts' => $contacts,
            'logs' => $logs
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getCampaignStats(int $id)
    {
        $total = BroadcastLog::where('campaign_id', $id)->count();
        $clicked = BroadcastLog::where('campaign_id', $id)->where('is_click', 1)->count();
        $sent = BroadcastLog::where('campaign_id', $id)->where('is_sent', 1)->count();
        $campaign = Campaign::with('recipient_list')->find($id);

        return [
            'recipient_list' => $campaign->recipient_list->name,
            'status' => $campaign->status,
            'messages' => [
                'total' => $total,
                'sent' => $sent,
                'clicked' => $clicked,
                'clicked_percentage' => $total > 0 ? ($clicked / $total) * 100 : 0,
                //'blocked' => $total - $sent, TODO: blocked
            ],
            'ctr' => $campaign->total_ctr,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function markAsProcessed(int $id)
    {
        // create message logs against each contact and generate the message acordingly
        $user = auth()->user();
        $campaign = Campaign::with(['user', 'message'])->withCount([
            'recipient_list as recipient_list_contacts_count' => function ($query) {
                $query->selectRaw('COUNT(DISTINCT contact_recipient_list.contact_id)')
                    ->join('contact_recipient_list', 'contact_recipient_list.recipients_list_id', '=', 'recipients_lists.id');
            }
        ])->findOrFail($id);

        $account = $campaign->user;
        $amount  = $campaign->recipient_list_contacts_count;

        if ($account->tokens < $amount) {
            return [false, 'You do not have enough tokens to process this campaign.'];
        }

        DB::beginTransaction();

        try {
            $recipientList  = $campaign->recipient_list;
            $recipientGroup = $recipientList->recipientsGroup;
            $batchSize      = 1000; // Number of records per batch
            $totalContacts  = $recipientGroup->count; // Total number of contacts
            $batches        = ceil($totalContacts / $batchSize); // Number of batches

            for ($i = 0; $i < $batches; $i++) {
                $offset = $i * $batchSize;
                $params = ['limit' => $batchSize, 'offset' => $offset, 'campaign' => $campaign, 'user' => $user];
                dispatch(new ProcessCampaign($params));
            }

            // Update campaign status
            $campaign->markAsProcessed();

            // Create a transaction record
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'usage',
            ]);

            // Deduct tokens from account
            $account->deductTokens($amount);
            $account->save();

            DB::commit();

            return [true, 'Job is being processed.'];
        } catch (\Exception $e) {
            DB::rollback();
            return [false, $e->getMessage()]; // TODO: remove after testing
        }
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @return Campaign
     */
    public function update(int $id, array $data)
    {
        $campaign = Campaign::find($id);
        $campaignFields = [
            'title',
            'description',
            'recipients_list_id',
        ];

        foreach ($data as $key => $field) {
            if (in_array($key, $campaignFields)) {
                $campaign->$key = $data[$key];
                unset($data[$key]);
            }
        }

        $campaign->generateUniqueFolder();
        $campaign->save();

        $campaign->message->update($data);

        return $campaign;
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function getCampaignsForUser(int $userId)
    {
        return $this->campaignRepository->getCampaignsForUser($userId);
    }

    /**
     * @param array $uniqCampaignIds
     * @param int $userId
     *
     * @return Collection
     */
    public function getUnsentByIdsOfUser(array $uniqCampaignIds, int $userId)
    {
        return $this->campaignRepository->getUnsentByIdsOfUser($uniqCampaignIds, $userId);
    }

    /**
     * @param array $uniqCampaignIds
     *
     * @return Collection
     */
    public function getUnsentByIds(array $uniqCampaignIds)
    {
        return $this->campaignRepository->getUnsentByIds($uniqCampaignIds);
    }
}
