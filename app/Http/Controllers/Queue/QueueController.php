<?php

declare(strict_types=1);

namespace App\Http\Controllers\Queue;

use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;

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
}
