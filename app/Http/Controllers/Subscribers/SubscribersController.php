<?php

declare(strict_types=1);

namespace App\Http\Controllers\Subscribers;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Events\SubscriberAddedEvent;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriberRequest;
use App\Models\UnsubscribeEventType;
use App\Repositories\SubscriberRepository;
use App\Repositories\TagRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Facades\MailSystem;

class SubscribersController extends Controller
{
    /** @var SubscriberRepository */
    private $subscriberRepo;

    /** @var TagRepository */
    private $tagRepo;

    public function __construct(SubscriberRepository $subscriberRepo, TagRepository $tagRepo)
    {
        $this->subscriberRepo = $subscriberRepo;
        $this->tagRepo = $tagRepo;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $subscribers = $this->subscriberRepo->paginate(
            MailSystem::currentWorkspaceId(),
            'email',
            ['tags'],
            50,
            request()->all()
        )->withQueryString();
        $tags = $this->tagRepo->pluck(MailSystem::currentWorkspaceId(), 'name', 'id');

        return view('subscribers.index', compact('subscribers', 'tags'));
    }

    /**
     * @throws Exception
     */
    public function create(): View
    {
        $tags = $this->tagRepo->pluck(MailSystem::currentWorkspaceId());
        $selectedTags = [];

        return view('subscribers.create', compact('tags', 'selectedTags'));
    }

    /**
     * @throws Exception
     */
    public function store(SubscriberRequest $request): RedirectResponse
    {
        $data = $request->all();
        $data['unsubscribed_at'] = $request->has('subscribed') ? null : now();
        $data['unsubscribe_event_id'] = $request->has('subscribed') ? null : UnsubscribeEventType::MANUAL_BY_ADMIN;

        $subscriber = $this->subscriberRepo->store(MailSystem::currentWorkspaceId(), $data);

        event(new SubscriberAddedEvent($subscriber));

        return redirect()->route('subscribers.index');
    }

    /**
     * @throws Exception
     */
    public function show(int $id): View
    {
        $subscriber = $this->subscriberRepo->find(
            0,
            $id,
            ['tags', 'messages.source']
        );

        return view('subscribers.show', compact('subscriber'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $subscriber = $this->subscriberRepo->find(MailSystem::currentWorkspaceId(), $id);
        $tags = $this->tagRepo->pluck(MailSystem::currentWorkspaceId());
        $selectedTags = $subscriber->tags->pluck('name', 'id');

        return view('subscribers.edit', compact('subscriber', 'tags', 'selectedTags'));
    }

    /**
     * @throws Exception
     */
    public function update(SubscriberRequest $request, int $id): RedirectResponse
    {
        $subscriber = $this->subscriberRepo->find(MailSystem::currentWorkspaceId(), $id);
        $data = $request->validated();

        // updating subscriber from subscribed -> unsubscribed
        if (!$request->has('subscribed') && !$subscriber->unsubscribed_at) {
            $data['unsubscribed_at'] = now();
            $data['unsubscribe_event_id'] = UnsubscribeEventType::MANUAL_BY_ADMIN;
        } // updating subscriber from unsubscribed -> subscribed
        elseif ($request->has('subscribed') && $subscriber->unsubscribed_at) {
            $data['unsubscribed_at'] = null;
            $data['unsubscribe_event_id'] = null;
        }

        if (!$request->has('tags')) {
            $data['tags'] = [];
        }

        $this->subscriberRepo->update(MailSystem::currentWorkspaceId(), $id, $data);

        return redirect()->route('subscribers.index');
    }

    /**
     * @throws Exception
     */
    public function destroy($id)
    {
        $subscriber = $this->subscriberRepo->find(MailSystem::currentWorkspaceId(), $id);

        $subscriber->delete();

        return redirect()->route('subscribers.index')->withSuccess('Subscriber deleted');
    }

    /**
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     * @throws Exception
     */
    public function export()
    {
        $subscribers = $this->subscriberRepo->all(MailSystem::currentWorkspaceId(), 'id');

        if (!$subscribers->count()) {
            return redirect()->route('subscribers.index')->withErrors(__('There are no subscribers to export'));
        }

        return (new FastExcel($subscribers))
            ->download(sprintf('subscribers-%s.csv', date('Y-m-d-H-m-s')), static function ($subscriber) {
                return [
                    'id' => $subscriber->id,
                    'hash' => $subscriber->hash,
                    'email' => $subscriber->email,
                    'first_name' => $subscriber->first_name,
                    'last_name' => $subscriber->last_name,
                    'created_at' => $subscriber->created_at,
                ];
            });
    }
}
