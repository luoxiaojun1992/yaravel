<?php

namespace App\Domains\Base\Repositories\Models\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;

trait ShortUuid
{
    /**
     * Scope by uuid
     *
     * @param $query
     * @param string $uuid uuid of the model.
     * @param bool $first
     * @return mixed
     */
    public function scopeShortUuid($query, $uuid, $first = true)
    {
        $originUUIR = new \PascalDeVink\ShortUuid\ShortUuid();

        $match = preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $originUUIR->decode($uuid));

        if (!is_string($uuid) || $match !== 1)
        {
            throw (new ModelNotFoundException())->setModel(get_class($this));
        }

        $results = $query->where(config('uuid.default_uuid_column'), $uuid);

        return $first ? $results->firstOrFail() : $results;
    }

    protected function setUUID()
    {
        if (!$this->{config('uuid.default_uuid_column')}) {
            $this->{config('uuid.default_uuid_column')} = \PascalDeVink\ShortUuid\ShortUuid::uuid4();
        }
    }

    protected function updateUUID()
    {
        $original_uuid = $this->getOriginal(config('uuid.default_uuid_column'));
        if ($original_uuid !== $this->{config('uuid.default_uuid_column')}) {
            $this->{config('uuid.default_uuid_column')} = $original_uuid;
        }
    }
}
