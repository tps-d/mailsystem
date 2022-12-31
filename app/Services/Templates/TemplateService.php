<?php

declare(strict_types=1);

namespace App\Services\Templates;

use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\Template;
use App\Repositories\TemplateRepository;
use App\Traits\NormalizeTags;

class TemplateService
{
    use NormalizeTags;

    /** @var TemplateRepository */
    private $templates;

    public function __construct(TemplateRepository $templates)
    {
        $this->templates = $templates;
    }

    /**
     * @throws Exception
     */
    public function store(int $workspaceId, array $data): Template
    {
        $data['content'] = $this->normalizeTags($data['content'], 'content');

        return $this->templates->store($workspaceId, $data);
    }

    /**
     * @throws Exception
     */
    public function update(int $workspaceId, int $templateId, array $data): Template
    {
        $data['content'] = $this->normalizeTags($data['content'], 'content');

        return $this->templates->update($workspaceId, $templateId, $data);
    }

    /**
     * @throws \Throwable
     */
    public function delete(int $workspaceId, int $templateId): bool
    {
        $template = $this->templates->find($workspaceId, $templateId);

        throw_if($template->isInUse(), ValidationException::withMessages([
            'template' => __('Cannot delete a template that has been used.')
        ]));

        return $this->templates->destroy($workspaceId, $templateId);
    }
}
