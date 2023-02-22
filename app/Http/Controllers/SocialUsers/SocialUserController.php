<?php

declare(strict_types=1);

namespace App\Http\Controllers\SocialUsers;

use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Rap2hpoutre\FastExcel\FastExcel;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialUsersRequest;
use App\Models\UnsubscribeEventType;
use App\Repositories\SocialUsersRepository;
use App\Repositories\TagRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Facades\MailSystem;

class SocialUserController extends Controller
{
    /** @var SocialUsersRepository */
    private $SocialUsersRepo;

    /** @var TagRepository */
    private $tagRepo;

    public function __construct(SocialUsersRepository $SocialUsersRepo, TagRepository $tagRepo)
    {
        $this->SocialUsersRepo = $SocialUsersRepo;
        $this->tagRepo = $tagRepo;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $socialUsers = $this->SocialUsersRepo->paginate(
            MailSystem::currentWorkspaceId(),
            'chat_id',
            [],
            50,
            request()->all()
        )->withQueryString();

        return view('socialusers.index', compact('socialUsers'));
    }

    /**
     * @throws Exception
     */
    public function create(): View
    {
        return view('socialusers.create');
    }

    /**
     * @throws Exception
     */
    public function store(SocialUsersRequest $request): RedirectResponse
    {
        $data = $request->all();
        $data['unsubscribed_at'] = $request->has('subscribed') ? null : now();
        $data['unsubscribe_event_id'] = $request->has('subscribed') ? null : UnsubscribeEventType::MANUAL_BY_ADMIN;

        $socialUsers = $this->SocialUsersRepo->store(MailSystem::currentWorkspaceId(), $data);

        return redirect()->route('socialusers.index');
    }

    /**
     * @throws Exception
     */
    public function show(int $id): View
    {
        $socialuser = $this->SocialUsersRepo->find(
            0,
            $id,
            []
        );

        return view('socialusers.show', compact('socialuser'));
    }

    /**
     * @throws Exception
     */
    public function edit(int $id): View
    {
        $socialuser = $this->SocialUsersRepo->find(MailSystem::currentWorkspaceId(), $id);

        return view('socialusers.edit', compact('socialuser'));
    }

    /**
     * @throws Exception
     */
    public function update(SocialUsersRequest $request, int $id): RedirectResponse
    {
        $socialuser = $this->SocialUsersRepo->find(MailSystem::currentWorkspaceId(), $id);
        $data = $request->validated();

        // updating socialuser from subscribed -> unsubscribed
        if (!$request->has('subscribed') && !$socialuser->unsubscribed_at) {
            $data['unsubscribed_at'] = now();
            $data['unsubscribe_event_id'] = UnsubscribeEventType::MANUAL_BY_ADMIN;
        } // updating socialuser from unsubscribed -> subscribed
        elseif ($request->has('subscribed') && $socialuser->unsubscribed_at) {
            $data['unsubscribed_at'] = null;
            $data['unsubscribe_event_id'] = null;
        }

        $this->SocialUsersRepo->update(MailSystem::currentWorkspaceId(), $id, $data);

        return redirect()->route('socialusers.index');
    }

    /**
     * @throws Exception
     */
    public function destroy($id)
    {
        $socialuser = $this->SocialUsersRepo->find(MailSystem::currentWorkspaceId(), $id);

        $socialuser->delete();

        return redirect()->route('socialusers.index')->withSuccess('socialuser deleted');
    }

}
