<?php

declare(strict_types=1);

namespace App\Http\Controllers\Campaigns;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Repositories\CampaignRepository;

use App\Facades\MailSystem;

class CampaignDeleteController extends Controller
{
    /** @var CampaignRepository */
    protected $campaigns;

    public function __construct(CampaignRepository $campaigns)
    {
        $this->campaigns = $campaigns;
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
            return redirect()->route('campaigns.index')
                ->withErrors(__('Unable to delete a campaign that is not in draft status'));
        }

        $this->campaigns->destroy(MailSystem::currentWorkspaceId(), $request->get('id'));

        return redirect()->route('campaigns.index')
            ->with('success', __('The Campaign has been successfully deleted'));
    }
}
