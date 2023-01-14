<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Presenters\CampaignReportPresenter;
use App\Repositories\CampaignRepository;
use App\Repositories\MessageRepository;

use App\Facades\MailSystem;

class CampaignReportsController extends Controller
{
    /** @var CampaignRepository */
    protected $campaignRepo;

    /** @var MessageRepository */
    protected $messageRepo;

    public function __construct(
        CampaignRepository $campaignRepository,
        MessageRepository $messageRepo
    ) {
        $this->campaignRepo = $campaignRepository;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Show campaign report view.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function index(int $id, Request $request)
    {
        $campaign = $this->campaignRepo->find(MailSystem::currentWorkspaceId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $presenter = new CampaignReportPresenter($campaign, MailSystem::currentWorkspaceId(), (int) $request->get('interval', 24));
        $presenterData = $presenter->generate();

        $data = [
            'campaign' => $campaign,
            'campaignUrls' => $presenterData['campaignUrls'],
            'campaignStats' => $presenterData['campaignStats'],
            'chartLabels' => json_encode(Arr::get($presenterData['chartData'], 'labels', [])),
            'chartData' => json_encode(Arr::get($presenterData['chartData'], 'data', [])),
        ];

        return view('campaigns.reports.index', $data);
    }

    /**
     * Show campaign recipients.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function recipients(int $id)
    {
        $campaign = $this->campaignRepo->find(MailSystem::currentWorkspaceId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->recipients(MailSystem::currentWorkspaceId(), Campaign::class, $id);

        return view('campaigns.reports.recipients', compact('campaign', 'messages'));
    }

    /**
     * Show campaign opens.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function opens(int $id)
    {
        $campaign = $this->campaignRepo->find(MailSystem::currentWorkspaceId(), $id);
        $averageTimeToOpen = $this->campaignRepo->getAverageTimeToOpen($campaign);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->opens(MailSystem::currentWorkspaceId(), Campaign::class, $id);

        return view('campaigns.reports.opens', compact('campaign', 'messages', 'averageTimeToOpen'));
    }

    /**
     * Show campaign clicks.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function clicks(int $id)
    {
        $campaign = $this->campaignRepo->find(MailSystem::currentWorkspaceId(), $id);
        $averageTimeToClick = $this->campaignRepo->getAverageTimeToClick($campaign);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->clicks(MailSystem::currentWorkspaceId(), Campaign::class, $id);

        return view('campaigns.reports.clicks', compact('campaign', 'messages', 'averageTimeToClick'));
    }

    /**
     * Show campaign bounces.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function bounces(int $id)
    {
        $campaign = $this->campaignRepo->find(MailSystem::currentWorkspaceId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->bounces(MailSystem::currentWorkspaceId(), Campaign::class, $id);

        return view('campaigns.reports.bounces', compact('campaign', 'messages'));
    }

    /**
     * Show campaign unsubscribes.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function unsubscribes(int $id)
    {
        $campaign = $this->campaignRepo->find(MailSystem::currentWorkspaceId(), $id);

        if ($campaign->draft) {
            return redirect()->route('campaigns.edit', $id);
        }

        if ($campaign->queued || $campaign->sending) {
            return redirect()->route('campaigns.status', $id);
        }

        $messages = $this->messageRepo->unsubscribes(MailSystem::currentWorkspaceId(), Campaign::class, $id);

        return view('campaigns.reports.unsubscribes', compact('campaign', 'messages'));
    }
}
