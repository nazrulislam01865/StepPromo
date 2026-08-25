<?php

namespace Tests\Feature;

use Tests\TestCase;

class MyWorkActiveTaskOnlyVisibilityTest extends TestCase
{
    public function test_my_tasks_uses_one_active_only_scope_for_all_roles(): void
    {
        $service = file_get_contents(app_path('Services/MyWorkService.php'));
        $component = file_get_contents(app_path('Livewire/MyWork/Index.php'));

        $this->assertStringContainsString('public function activeVisibleTaskQuery(User $user): Builder', $service);
        $this->assertStringContainsString('$query = Task::query();', $service);
        $this->assertStringContainsString('if (!$access->isAdministrator($user))', $service);
        $this->assertStringContainsString("->where('tasks.assignee_id', $user->id)", $service);
        $this->assertStringContainsString("->orWhereHas('job', fn (Builder $job) => $job->where('created_by', $user->id))", $service);
        $this->assertStringContainsString("->orWhereIn('tasks.id', $visibleByConfiguredAccess)", $service);
        $this->assertStringContainsString("->whereNull('tasks.completed_at')", $service);
        $this->assertStringContainsString("->whereColumn('flow_jobs.workflow_phase_id', 'tasks.workflow_phase_id')", $service);
        $this->assertStringContainsString("'waiting for sample approval'", $service);
        $this->assertStringContainsString("'waiting for qc issue resolution'", $service);

        // Admin/Super Admin also need the table to advance to the next active row.
        $this->assertStringContainsString("\$result['refresh'] = true;", $component);
    }

    public function test_stage_cards_and_counters_use_the_same_active_scope(): void
    {
        $service = file_get_contents(app_path('Services/MyWorkService.php'));

        $this->assertStringContainsString('$counts = $this->personalTaskQuery($user, [])', $service);
        $this->assertStringContainsString('$activeVisibleTaskIds = $this->activeVisibleTaskQuery($user)', $service);
        $this->assertStringContainsString("->whereIn('tasks.id', $activeVisibleTaskIds)", $service);
    }
}
