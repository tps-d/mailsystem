<?php

namespace App\Repositories;

use App\Models\Variable;

class VariableRepository extends BaseRepository
{
    /**
     * @var string
     */
    protected $modelName = Variable::class;

    /**
     * {@inheritDoc}
     */
    public function update($workspaceId, $id, array $data)
    {
        $instance = $this->find($workspaceId, $id);

        $this->executeSave($workspaceId, $instance, $data);

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($workspaceId, $id): bool
    {
        $instance = $this->find($workspaceId, $id);
        return $instance->delete();
    }
}
