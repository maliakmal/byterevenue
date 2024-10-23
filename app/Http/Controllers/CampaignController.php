<?php

namespace App\Http\Controllers;

use App\Models\RecipientsList;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\User;
use App\Models\BroadcastLog;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignRepositoryInterface $campaignRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $campaigns = Campaign::query();
        $filter = [
            'status' => request('status'),
            'user_id' => request('user_id'),
            'sortby' => request('sortby', 'id_desc'),
            'count' => request('count', 5),
        ];


        if (!is_null($filter['status'])) {
            $campaigns->where('status', $filter['status']);
        }

        if (auth()->user()->hasRole('admin')) {

            if (!empty($filter['user_id'])) {
                $campaigns->where('user_id', $filter['user_id']);
            }

        } else {
            $campaigns->where('user_id', auth()->id());
        }

        if (!empty($filter['sortby'])) {
            switch ($filter['sortby']) {
                case 'id_desc':
                    $campaigns->orderby('id', 'desc');
                    break;
                case 'id_asc':
                    $campaigns->orderby('id', 'asc');
                    break;
                case 'ctr_desc':
                    $campaigns->orderby('total_ctr', 'desc');
                    break;
                case 'ctr_asc':
                    $campaigns->orderby('total_ctr', 'asc');
                    break;
                case 'clicks_desc':
                    $campaigns->orderby('total_recipients_click_thru', 'desc');
                    break;
                case 'clicks_asc':
                    $campaigns->orderby('total_recipients_click_thru', 'asc');
                    break;
                case 'title':
                    $campaigns->orderby('title', 'asc');
                    break;
            }
        }
        $campaigns = $campaigns->paginate($filter['count']);

        return view('campaigns.index', compact('campaigns', 'filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $recipient_lists = auth()->user()->recipientLists()->get();
        return view('campaigns.create', compact('recipient_lists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|string|max:255',
        ]);
        try {
            DB::beginTransaction();
            $campaign = auth()->user()->campaigns()->create([
                'title' => $request->title,
                'description' => $request->description,
                'recipients_list_id' => $request->recipients_list_id,
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
            return redirect()->route('campaigns.index')->with('error', $exception->getMessage());
        } catch (\Exception $exception) {
            DB::rollBack();
            report($exception);
            return redirect()->route('campaigns.index')->with('error', 'Error Create Campaign');
        }
        $message_data = [
            'subject' => $request->message_subject,
            'body' => $request->message_body,
            'target_url' => $request->message_target_url,
            "user_id" => auth()->id(),
            'campaign_id' => $campaign->id
        ];
        Message::create($message_data);
        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $per_page = 15;
        $message = $campaign->message;

        $recipient_lists = $campaign->recipient_list;

        if ($campaign->isDraft()) {
            $contacts = $recipient_lists->contacts()->paginate($per_page);
            $logs = [];

        } else {
            $contacts = [];
            $logs = BroadcastLog::where('campaign_id', $campaign->id)->paginate($per_page);
        }
        if (request()->input('output') == 'json') {
            return response()->success(null, [
                'contacts' => $contacts,
                'logs' => $logs,
            ]);
        }
        return view('campaigns.show', compact('campaign', 'contacts', 'logs', 'message'));

    }

    public function createBroadcastBatch(Campaign $campaign)
    {
        return view('campaigns.broadcast-batch.create', compact('campaign', 'recipient_lists'));
    }

    public function markAsProcessed($id)
    {
        // create message logs against each contact and generate the message acordingly
        $campaign = Campaign::with(['user'])->withCount([
            'recipient_list as recipient_list_contacts_count' => function ($query) {
                $query->selectRaw('COUNT(DISTINCT contact_recipient_list.contact_id)')
                    ->join('contact_recipient_list', 'contact_recipient_list.recipients_list_id', '=', 'recipients_lists.id');
            }
        ])->findOrFail($id);
        $account = $campaign->user;
        $amount = $campaign->recipient_list_contacts_count;
        if ($account->tokens < $amount) {
            return redirect()->back()->withErrors(['error' => 'You do not have enough tokens to process this campaign.']);
        }
        DB::enableQueryLog();

        DB::beginTransaction();

        try {
            //$message = $campaign->message->getParsedMessage();


            $sql = "INSERT INTO broadcast_logs ";
            $sql .= "(contact_id, recipient_phone,  user_id, recipients_list_id, message_id, message_body, is_downloaded_as_csv, campaign_id, created_at, updated_at) ";
            $sql .= "SELECT id, phone, ?, ?, ?, '', ?, ?,  NOW(), NOW() from contacts where contacts.id in (select contact_id from contact_recipient_list where recipients_list_id = ?) ";
            //var_dump(sprintf($sql, auth()->id(), $campaign->recipient_list->id, $campaign->message->id, '', '', 0, $campaign->id));die();
            $recepientListId = $campaign->recipient_list->id;
            DB::insert($sql, [auth()->id(), $recepientListId, $campaign->message->id, 0, $campaign->id, $recepientListId]);


            // $data = [
            //     'user_id'=>auth()->id(),
            //     'recipients_list_id'=>$recepientListId,
            //     'message_id'=>$campaign->message->id,
            //     'message_body'=>'',
            //     'recipient_phone'=>'',
            //     'contact_id'=>0,
            //     'is_downloaded_as_csv'=>0,
            //     'campaign_id'=>$campaign->id,
            // ];

            // $contacts = $campaign->recipient_list->contacts()->get();

            // foreach($contacts as $contact){
            //     $data['recipient_phone'] = $contact->phone;
            //     $data['contact_id'] = $contact->id;
            //     BroadcastLog::create($data);
            // }
//
            $campaign->markAsProcessed();
            $campaign->save();

            Transaction::create([
                'user_id' => auth()->id(),
                'amount' => $amount,
                'type' => 'usage',
            ]);
            $account->deductTokens($amount);
            $account->save();

            DB::commit();

            $queries = DB::getQueryLog();


            return redirect()->back()->with('success', 'Job is being processed.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'An error occurred - please try again later.']);
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        $recipient_lists = RecipientsList::where('user_id', $campaign->user_id)->get();
        return view('campaigns.edit', compact('campaign', 'recipient_lists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'recipients_list_id' => 'required|integer|exists:recipients_lists,id',
            'message_subject' => 'required|string|max:255',
            'message_body' => 'required|string',
            'message_target_url' => 'nullable|url',
        ]);
        $campaign->fill([
            'title' => $request->title,
            'description' => $request->description,
            'recipients_list_id' => $request->recipients_list_id,
        ]);

        $campaign->generateUniqueFolder();
        $campaign->save();

        $campaign->message->update([
            'subject' => $request->message_subject,
            'body' => $request->message_body,
            'target_url' => $request->message_target_url,
        ]);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign deleted successfully.');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getCampaignForUser(Request $request)
    {
        $request->validate(['user_id' => 'required|numeric']);
        $user_id = $request->user_id;
        $campaigns = $this->campaignRepository->getCampaignsForUser($user_id);
        return response()->success(null, $campaigns);

    }
}
