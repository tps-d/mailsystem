<?php

declare(strict_types=1);

namespace App\Http\Controllers\Automations;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutomationsStoreRequest;
use App\Models\EmailService;
use App\Repositories\AutomationsRepository;
use App\Repositories\EmailServiceRepository;
use App\Repositories\SubscriberRepository;
use App\Repositories\TagRepository;
use App\Repositories\TemplateRepository;
use App\Services\Campaigns\CampaignStatisticsService;

use App\Facades\MailSystem;

class AutomationsController extends Controller
{
    /** @var AutomationsRepository */
    protected $automations;

    /** @var TemplateRepository */
    protected $templates;

    /** @var TagRepository */
    protected $tags;

    /** @var EmailServiceRepository */
    protected $emailServices;

    /** @var SubscriberRepository */
    protected $subscribers;

    /**
     * @var CampaignStatisticsService
     */
    protected $campaignStatisticsService;

    public function __construct(
        AutomationsRepository $automations,
        TemplateRepository $templates,
        TagRepository $tags,
        EmailServiceRepository $emailServices,
        SubscriberRepository $subscribers,
        CampaignStatisticsService $campaignStatisticsService
    ) {
        $this->automations = $automations;
        $this->templates = $templates;
        $this->tags = $tags;
        $this->emailServices = $emailServices;
        $this->subscribers = $subscribers;
        $this->campaignStatisticsService = $campaignStatisticsService;
    }

    /**
     * @throws Exception
     */
    public function index(): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $params = ['draft' => true];
        $campaigns = $this->automations->paginate($workspaceId, 'created_atDesc', ['status'], 25, $params);

        return view('automations.index', [
            'automations' => $campaigns,
            //'campaignStats' => $this->campaignStatisticsService->getForPaginator($campaigns, $workspaceId),
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
                $emailService->formatted_name = "{$emailService->name} ({$emailService->type->name})";
                return $emailService;
            });

        $subscriberCount = $this->subscribers->countActive($workspaceId);

        $tags = $this->tags->all($workspaceId, 'name');

        return view('automations.create', compact('templates', 'emailServices', 'tags', 'subscriberCount'));
    }

    /**
     * @throws Exception
     */
    public function store(AutomationsStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->automations->store($workspaceId, $this->handleCheckboxes($request->validated()));

        return redirect()->route('automations.preview', $campaign->id);
    }

    /**
     * @throws Exception
     */
    public function show(int $id): ViewContract
    {
        $campaign = $this->automations->find(MailSystem::currentWorkspaceId(), $id);

        return view('automations.show', compact('campaign'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): ViewContract
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->automations->find($workspaceId, $id);
        $emailServices = $this->emailServices->all($workspaceId, 'id', ['type'])
            ->map(static function (EmailService $emailService) {
                $emailService->formatted_name = "{$emailService->name} ({$emailService->type->name})";
                return $emailService;
            });
        $templates = [null => '- None -'] + $this->templates->pluck($workspaceId);

        return view('automations.edit', compact('campaign', 'emailServices', 'templates'));
    }

    /**
     * @throws Exception
     */
    public function update(int $campaignId, AutomationsStoreRequest $request): RedirectResponse
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->automations->update(
            $workspaceId,
            $campaignId,
            $this->handleCheckboxes($request->validated())
        );

        return redirect()->route('automations.preview', $campaign->id);
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function preview(int $id)
    {
        $campaign = $this->automations->find(MailSystem::currentWorkspaceId(), $id);
        $subscriberCount = $this->subscribers->countActive(MailSystem::currentWorkspaceId());

        if (!$campaign->draft) {
            return redirect()->route('automations.status', $id);
        }

        $tags = $this->tags->all(MailSystem::currentWorkspaceId(), 'name');

        return view('automations.preview', compact('campaign', 'tags', 'subscriberCount'));
    }

    /**
     * @return RedirectResponse|ViewContract
     * @throws Exception
     */
    public function status(int $id)
    {
        $workspaceId = MailSystem::currentWorkspaceId();
        $campaign = $this->automations->find($workspaceId, $id, ['status']);

        if ($campaign->sent) {
            return redirect()->route('automations.reports.index', $id);
        }

        return view('automations.status', [
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
