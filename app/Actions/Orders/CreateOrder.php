<?php

namespace App\Actions\Orders;

use App\DTOs\Orders\OrderCreateData;
use App\Models\FlowJob;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\Orders\OrderLifecycleService;

/** Create an Order from already validated Create Order form data. */
final class CreateOrder
{
    public function __construct(
        private readonly OrderLifecycleService $jobs,
        private readonly DocumentService $documents,
    ) {
    }

    public function handle(array $data, array $attachments, bool $draft, User $actor): FlowJob
    {
        $payload = OrderCreateData::fromLivewire($data, $draft);

        $job = $this->jobs->create($payload->toArray(), $actor);

        foreach ($attachments as $upload) {
            $this->documents->store($upload, [
                'flow_job_id' => $job->id,
                'client_id' => $job->client_id,
                'category' => 'Order attachment',
            ], $actor);
        }

        return $job;
    }
}
