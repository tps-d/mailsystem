<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Repositories\CampaignRepository;
use App\Repositories\AutomationsRepository;

use App\Facades\MailSystem;

class CampaignDeleteController extends Controller
{
    /** @var CampaignRepository */
    protected $campaigns;

    /** @var AutomationsRepository */
    protected $automations;

    public function __construct(CampaignRepository $campaigns, AutomationsRepository $automations)
    {
        $this->campaigns = $campaigns;
        $this->automations = $automations;
    }

    /**
     * Show a confirmation view prior to deletion.
     *
     * @return RedirectResponse|View
     * @throws Exception
     */
    public function confirm(int $id)
    {
        $campaign = $this->campaigns->find(MailSystem::currentWorkspaceId(), $id);

        if (!$campaign->draft) {
            return redirect()->route('campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        return view('campaigns.delete', compact('campaign'));
    }

    /**
     * Delete a campaign from the database.
     *
     * @throws Exception
     */
    public function destroy(Request $request): RedirectResponse
    {
        $campaign = $this->campaigns->find(MailSystem::currentWorkspaceId(), $request->get('id'));

        if (!$campaign->draft) {
            return redirect()->route('campaigns.destroy.confirm',$campaign->id)
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        $automation = $this->automations->getBy(MailSystem::currentWorkspaceId(), ['campaign_id' => $campaign->id]);
        if (!empty($automation->toArray())) {
            return redirect()->route('campaigns.destroy.confirm',$campaign->id)
                ->withErrors(__('You cannot delete this campaign that is currently used by a automation task'));
        }

        $this->campaigns->destroy(MailSystem::currentWorkspaceId(), $request->get('id'));

        return redirect()->route('campaigns.index')
            ->with('success', __('The Campaign has been successfully deleted'));
    }
}
