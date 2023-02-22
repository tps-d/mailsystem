<?php

declare(strict_types=1);

namespace App\Http\Controllers\SocialServices;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialServiceRequest;
use App\Repositories\SocialServiceRepository;

use App\Facades\MailSystem;

use Telegram\Bot\Api;

class SocialServicesController extends Controller
{
    /** @var SocialServiceRepository */
    private $socialServices;

    public function __construct(SocialServiceRepository $socialServices)
    {
        $this->socialServices = $socialServices;
    }

    /**
     * @throws Exception
     */
    public function index(): View
    {
        $socialServices = $this->socialServices->all(MailSystem::currentWorkspaceId());

        return view('social_services.index', compact('socialServices'));
    }

    public function create(): View
    {
        $socialServiceTypes = $this->socialServices->getSocialServiceTypes()->pluck('name', 'id');

        return view('social_services.create', compact('socialServiceTypes'));
    }

    /**
     * @throws Exception
     */
    public function store(SocialServiceRequest $request): RedirectResponse
    {
        $socialServiceType = $this->socialServices->findType($request->type_id);

        $settings = $request->get('settings', []);

        $this->socialServices->store(MailSystem::currentWorkspaceId(), [
            'name' => $request->name,
            'type_id' => $socialServiceType->id,
            'bot_id' => $request->bot_id,
            'bot_username' => $request->bot_username,
            'settings' => $settings,
        ]);

        return redirect()->route('social_services.index');
    }

    /**
     * @throws Exception
     */
    public function edit(int $socialServiceId)
    {
        $socialServiceTypes = $this->socialServices->getSocialServiceTypes()->pluck('name', 'id');
        $socialService = $this->socialServices->find(MailSystem::currentWorkspaceId(), $socialServiceId);
        $socialServiceType = $this->socialServices->findType($socialService->type_id);

        return view('social_services.edit', compact('socialServiceTypes', 'socialService', 'socialServiceType'));
    }

    /**
     * @throws Exception
     */
    public function update(SocialServiceRequest $request, int $socialServiceId): RedirectResponse
    {
        $sociaService = $this->socialServices->find(MailSystem::currentWorkspaceId(), $socialServiceId, ['type']);

        $settings = $request->get('settings');

        $sociaService->name = $request->name;
        $sociaService->bot_id = $request->bot_id;
        $sociaService->bot_username = $request->bot_username;
        $sociaService->settings = $settings;
        $sociaService->save();

        return redirect()->route('social_services.index');
    }

    /**
     * @throws Exception
     */
    public function delete(int $socialServiceId): RedirectResponse
    {
        $sociaService = $this->socialServices->find(MailSystem::currentWorkspaceId(), $socialServiceId, ['campaigns']);

        if ($sociaService->in_use) {
            return redirect()->back()->withErrors(__("You cannot delete an email service that is currently used by a campaign or automation."));
        }

        $this->socialServices->destroy(MailSystem::currentWorkspaceId(), $socialServiceId);

        return redirect()->route('social_services.index');
    }

    public function socialServicesTypeAjax($socialServiceTypeId): JsonResponse
    {
        $socialServiceType = $this->socialServices->findType($socialServiceTypeId);

        $view = view()
            ->make('social_services.options.' . strtolower($socialServiceType->name))
            ->render();

        return response()->json([
            'view' => $view
        ]);
    }

/*
    public function test(int $socialServiceId)
    {
        $socialServices = $this->socialServices->find(MailSystem::currentWorkspaceId(), $socialServiceId);

        $token = $socialServices->settings['token'];

        $telegram = new Api($token);
        $response = $telegram->getMe();

        $botId = $response->getId();
        $firstName = $response->getFirstName();
        $username = $response->getUsername();

        print_r([
            'botId' => $botId,
            'firstName' => $firstName,
            'username' => $username
        ]);
    }
    */
}
