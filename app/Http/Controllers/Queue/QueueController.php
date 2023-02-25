<?php

declare(strict_types=1);

namespace App\Http\Controllers\Queue;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\Controller;

use App\Models\Jobs;
use App\Models\FailedJobs;

class QueueController extends Controller
{

    /**
     * @throws Exception
     */
    public function dispatch_jobs(): ViewContract
    {
        $query = (new Jobs())->newQuery();
        $jobs = $query->where('queue', 'message-dispatch')->orderBy('created_at', 'desc')->paginate(25);

        return view('queue.queue', [
            'jobs' => $jobs
        ]);
    }

    /**
     * @throws Exception
     */
    public function webhook_jobs(): ViewContract
    {
        $query = (new Jobs())->newQuery();
        $jobs = $query->where('queue', 'webhook-process')->orderBy('created_at', 'desc')->paginate(25);

        return view('queue.queue', [
            'jobs' => $jobs
        ]);
    }

    /**
     * @throws Exception
     */
    public function failed_jobs(): ViewContract
    {
        $query = (new FailedJobs())->newQuery();
        $jobs = $query->orderBy('failed_at', 'desc')->paginate(25);

        return view('queue.failed_jobs', [
            'jobs' => $jobs
        ]);
    }

    public function retry(int $jobId): RedirectResponse
    {
        $failedjob = FailedJobs::where('id',$jobId)->first();
        if (!$failedjob) {
            return redirect()->route('queue.failed')
                ->withErrors(__('Unknow failed job with id '.$jobId));
        }

        Artisan::call('queue:retry', ['id' => $failedjob->id]);

        return redirect()->route('queue.failed')
            ->with('success', __('The Job has been successfully retried'));
    }
    
    public function delete(int $jobId): RedirectResponse
    {
        $failedjob = FailedJobs::where('id',$jobId)->first();
        if (!$failedjob) {
            return redirect()->route('queue.failed')
                ->withErrors(__('Unknow failed job with id '.$jobId));
        }

        $failedjob->delete();

        return redirect()->route('queue.failed')
            ->with('success', __('The Job has been successfully deleted'));
    }
}
