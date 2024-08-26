<?php

namespace App\Http\Controllers;

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
        protected  CampaignRepositoryInterface $campaignRepository
    )
    {
    }

    /**
         * Display a listing of the resource.
         */
        public function index()
        {
            $campaigns = Campaign::query();
            $filter = array(
                'status'=> request('status')!=''?request('status'):null,
                'user_id'=> request('user_id')?request('user_id'):null,
                'sortby'=> request('sortby')?request('sortby'):'id_desc',
                'count'=> request('count')?request('count'):5,
            );


            if(!is_null($filter['status'])){
                $campaigns->where('status', $filter['status']);
            }

            if(auth()->user()->hasRole('admin')){

                if(!empty($filter['user_id'])){
                    $campaigns->where('user_id', $filter['user_id']);
                }

            }else{
                $campaigns->where('user_id', auth()->user()->id);
            }

            if(!empty($filter['sortby'])){
                switch($filter['sortby']){
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
            $clients = auth()->user()->clients->all();
            $recipient_lists = auth()->user()->recipientLists()->get()->all();
            return view('campaigns.create', compact('clients', 'recipient_lists'));
        }

        /**
         * Store a newly created resource in storage.
         */
        public function store(Request $request)
        {

            $request->validate([
                'title' => 'required|string|max:255',
            ]);
            $inputs = $request->all();
            try{
                DB::beginTransaction();
                $campaign = auth()->user()->campaigns()->create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'recipients_list_id' => $request->recipients_list_id,
                ]);
                $campaign->generateUniqueFolder();
                $campaign->save();
                if(auth()->user()->show_introductory_screen == true){
                    User::where('id', auth()->id())->update(['show_introductory_screen' => false]);
                }
//                $caller = new KeitaroCaller();
//                $create_group_request = new CreateGroupRequest($campaign->title, 'campaigns');
//                $response = $caller->call($create_group_request);
//                $campaign->keitaro_group_id = $response['id'];
//                $campaign->keitaro_create_group_response = @json_encode($response);
//                $campaign->save();
                DB::commit();
            }
            catch (RequestException $exception){
                DB::rollBack();
                return redirect()->route('campaigns.index')->with('error', $exception->getMessage());
            }
            catch (\Exception $exception){
                DB::rollBack();
                report($exception);
                return redirect()->route('campaigns.index')->with('error', 'Error Create Campaign');
            }
            $message_data = [
                'subject'=>$request->message_subject,
                'body'=>$request->message_body,
                'target_url'=>$request->message_target_url,
                "user_id"=>auth()->user()->id,
                'campaign_id'=>$campaign->id
            ];
            $message = Message::create($message_data);
            return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign created successfully.');
        }

        /**
         * Display the specified resource.
         */
        public function show(Campaign $campaign)
        {
            $message = $campaign->message;

            $recipient_lists = $campaign->recipient_list;

            if($campaign->isDraft()){
                $contacts = $recipient_lists->contacts()->paginate(10);
                $logs = [];

            }else{
                $contacts = [];
                $logs = BroadcastLog::select()->where('campaign_id', '=', $campaign->id)->paginate(10);
            }
            return view('campaigns.show', compact('campaign', 'contacts', 'logs', 'message', 'recipient_lists'));

        }

        public function createBroadcastBatch(Campaign $campaign)
        {
            return view('campaigns.broadcast-batch.create', compact('campaign', 'recipient_lists'));
        }

        public function markAsProcessed($id){
            // create message logs against each contact and generate the message acordingly
            $campaign = Campaign::findOrFail($id);
            $account = User::find($campaign->user_id);
            $amount = $campaign->recipient_list->contacts()->count();
            if($account->tokens < $amount){
                return redirect()->back()->withErrors(['error' => 'You do not have enough tokens to process this campaign.']);
            }
            DB::enableQueryLog();

            DB::beginTransaction();

            try {
                $campaign = Campaign::findOrFail($id);

                //$message = $campaign->message->getParsedMessage();


                $sql = "INSERT INTO broadcast_logs ";
                $sql.="(contact_id, recipient_phone,  user_id, recipients_list_id, message_id, message_body, is_downloaded_as_csv, campaign_id, created_at, updated_at) ";
                $sql.="SELECT id, phone, ?, ?, ?, '', ?, ?,  NOW(), NOW() from contacts where contacts.id in (select contact_id from contact_recipient_list where recipients_list_id = ?) ";
                //var_dump(sprintf($sql, auth()->id(), $campaign->recipient_list->id, $campaign->message->id, '', '', 0, $campaign->id));die();
                DB::insert($sql, [auth()->id(), $campaign->recipient_list->id, $campaign->message->id, 0, $campaign->id, $campaign->recipient_list->id]);


                // $data = [
                //     'user_id'=>auth()->id(),
                //     'recipients_list_id'=>$campaign->recipient_list->id,
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

                $account = User::find(auth()->user()->id);
                $amount = $campaign->recipient_list->contacts()->count();
                Transaction::create([
                    'user_id'=>$account->id,
                    'amount'=>$amount,
                    'type'=>'usage',
                ]);
                $account->deductTokens($amount);
                $account->save();

                DB::commit();

                $queries = DB::getQueryLog();


                return redirect()->back()->with('success', 'Job is being processed.');
            } catch (\Exception $e) {
                DB::rollback();
                var_dump($e);die();
                return redirect()->back()->withErrors(['error' => 'An error occurred - please try again later.']);
            }

        }

        /**
         * Show the form for editing the specified resource.
         */
        public function edit(Campaign $campaign)
        {
            $user = $campaign->user;
            $recipient_lists = $user->recipientLists()->get()->all();
            return view('campaigns.edit', compact('campaign', 'recipient_lists'));
        }

        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, Campaign $campaign)
        {
            $request->validate([
                'title' => 'required|string|max:255',
            ]);

            $campaign->title = $request->title;
            $campaign->description = $request->description;
            $campaign->recipients_list_id = $request->recipients_list_id;
            $campaign->save();

            $campaign->generateUniqueFolder();
            $campaign->save();

            $message = $campaign->message;
            $message->subject = $request->message_subject;
            $message->body = $request->message_body;
            $message->target_url = $request->message_target_url;
            $message->save();


            return redirect()->route('campaigns.show', $campaign)->with('success', 'Campaign updated successfully.');
        }

        /**
         * Remove the specified resource from storage.
         */
        public function destroy(Campaign $campaign)
        {
            $client->delete();

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
