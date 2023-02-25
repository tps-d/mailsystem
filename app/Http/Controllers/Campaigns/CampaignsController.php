<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignStoreRequest;
use App\Models\EmailService;
use App\Models\SocialService;
use App\Models\Campaign;
use App\Models\CampaignStatus;
use App\Repositories\CampaignRepository;
use App\Repositories\EmailServiceRepository;
use App\Repositories\SubscriberRepository;

use App\Repositories\SocialServiceRepository;
use App\Repositories\SocialUsersRepository;
use App\Repositories\TagRepository;
use App\Repositories\TemplateRepository;
use App\Services\Campaigns\CampaignStatisticsService;

use App\Facades\MailSystem;

class CampaignsController extends Controller
{
    /** @var CampaignRepository */
    protected $campaigns;

    /** @var TemplateRepository */
    protected $templates;

    /** @var TagRepository */
    protected $tags;

    /** @var EmailServiceRepository */
    protected $emailServices;

    /** @var SubscriberRepository */
    protected $subscribers;

    /** @var SocialServiceRepository */
    protected $socialServices;

    /** @var SocialUsersRepository */
    protected $socialusers;

    /**
     * @var CampaignStatisticsService
     */
    protected $campaignStatisticsService;

    public function __construct(
        CampaignRepository $campaigns,
        TemplateRepository $templates,
        TagRepository $tags,
        EmailServiceRepository $emailServices,
        SubscriberRepository $subscribers,

        SocialServiceRepository $socialServices,
        SocialUsersRepository $socialusers,
        CampaignStatisticsService $campaignStatisticsService
    ) {
        $this->campaigns = $campaigns;
        $this->templates = $templates;
        $this->tags = $tags;
        $this->emailServices = $emailServices;
        $this->subscribers = $subscribers;
        $this->socialServices = $socialServices;
        $this->socialusers = $socialusers;
        $this->campaignStatisticsService = $campaignStatisticsService;
    }

    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $params = ['draft' => true];
        $campaigns = $this->campaigns->paginate($workspaceId, 'created_atDesc', ['status'], 25, $params);

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
        ]);
    }

    /**
     * @throws Exception
     */
    public function sent(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $params = ['sent' => true];
        $campaigns = $this->campaigns->paginate($workspaceId, 'created_atDesc', ['status'], 25, $params);

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
        ]);
    }

    /**
     * @throws Exception
     */
    public function listen(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $params = ['repeat' => true];
        $campaigns = $this->campaigns->paginate($workspaceId, 'created_atDesc', ['status'], 25, $params);

        return view('campaigns.index', [
            'campaigns' => $campaigns,
            'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
        ]);
    }

    /**
     * @throws Exception
     */
    public function create(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);
        $emailServices = $this->emailServices->all($workspaceId, 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->from_name} <{$emailService->from_email}> ({$emailService->type->name})";
                return $emailService;
            });

        $subscriberCount = $this->subscribers->countActive($workspaceId);

        $tags = $this->tags->all($workspaceId, 'name');

        $socialServices = $this->socialServices->all($workspaceId, 'id', ['type'])
            ->map(static function (SocialService $socialServices) {
                $socialServices->formatted_name = "@{$socialServices->bot_username} ({$socialServices->type->name})";
                return $socialServices;
            });
        $socialuserCount = $this->socialusers->countActive($workspaceId);

        $campaign = new Campaign();
        return view('campaigns.create', compact('campaign','templates', 'emailServices', 'tags', 'subscriberCount', 'socialServices','socialuserCount'));
    }

    /**
     * @throws Exception
     */
    public function store(CampaignStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();

        $input = $this->handleCheckboxes($request->validated());
        $input['send_to_all'] =  $request->get('recipients') === 'send_to_all';
        //$input['scheduled_at'] = null;
        $input['status_id'] = CampaignStatus::STATUS_DRAFT;
        $input['save_as_draft'] = false;

        unset($input['tags']);

        $campaign = $this->campaigns->store($workspaceId, $input);

        $campaign->tags()->sync($request->get('tags'));

        return redirect()->route('campaigns.preview', $campaign->id);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): ViewContract
    {
        $campaign = $this->campaigns->find(MailSystem::currentWorkspaceId(), $id);

        return view('campaigns.show', compact('campaign'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->campaigns->find($workspaceId, $id);
        $emailServices = $this->emailServices->all($workspaceId, 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->name} ({$emailService->type->name})";
                return $emailService;
            });
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);

        $subscriberCount = $this->subscribers->countActive($workspaceId);

        $tags = $this->tags->all($workspaceId, 'name');

        $socialServices = $this->socialServices->all($workspaceId, 'id', ['type'])
            ->map(static function (SocialService $socialServices) {
                $socialServices->formatted_name = "@{$socialServices->bot_username} ({$socialServices->type->name})";
                return $socialServices;
            });
        $socialuserCount = $this->socialusers->countActive($workspaceId);

        return view('campaigns.edit', compact('campaign', 'emailServices', 'socialServices','templates','tags','subscriberCount','socialuserCount'));
    }

    /**
     * @throws Exception
     */
    public function update(int $campaignId, CampaignStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();

        $input = $this->handleCheckboxes($request->validated());
        $input['send_to_all'] =  $request->get('recipients') === 'send_to_all';

        if(!$request->get('is_send_mail')){
            $input['is_send_mail'] = false;
            $input['subject'] = '';
            $input['email_service_id'] = 0;
            $input['is_open_tracking'] = 0;
            $input['is_click_tracking'] = 0;
        }

        if(!$request->get('is_send_social')){
            $input['is_send_social'] = false;
            $input['social_service_id'] = 0;
        }

        unset($input['tags']);

        $campaign = $this->campaigns->update(
            $workspaceId,
            $campaignId,
            $input
        );

        $campaign->tags()->sync($request->get('tags'));

        return redirect()->route('campaigns.preview', $campaign->id);
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function preview(int $id)
    {
        $campaign = $this->campaigns->find(MailSystem::currentWorkspaceId(), $id, ['email_service','social_service']);
        $subscriberCount = $this->subscribers->countActive(MailSystem::currentWorkspaceId());

        //if (!$campaign->draft) {
        //    return redirect()->route('campaigns.status', $id);
        //}

        return view('campaigns.preview', compact('campaign'));
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function status(int $id)
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->campaigns->find($workspaceId, $id, ['status']);

        //if ($campaign->sent) {
        //    return redirect()->route('campaigns.reports.index', $id);
        //}

        return view('campaigns.status', [
            'campaign' => $campaign,
            'campaignStats' => $this->campaignStatisticsService->getForCampaign($campaign, $workspaceId),
        ]);
    }

    /**
     * Handle checkbox fields.
     *
     * NOTE(david): this is here because the Campaign model is marked as being unable to use boolean fields.
     */
    private function handleCheckboxes(array $input): array
    {
        $checkboxFields = [
            'is_open_tracking',
            'is_click_tracking'
        ];

        foreach ($checkboxFields as $checkboxField) {
            if (!isset($input[$checkboxField])) {
                $input[$checkboxField] = false;
            }
        }

        return $input;
    }
}
