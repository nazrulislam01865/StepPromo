<?php

namespace Tests\Feature;

use Tests\TestCase;

class SharedRemoteDropdownStabilityTest extends TestCase
{
    public function test_all_remote_search_dropdowns_reopen_with_a_compact_recent_page(): void
    {
        $runtime = file_get_contents(resource_path('js/components/list-filters.js'));

        $this->assertStringContainsString('const REMOTE_RECENT_PAGE_SIZE = 5;', $runtime);
        $this->assertStringContainsString('const REMOTE_SEARCH_PAGE_SIZE = 20;', $runtime);
        $this->assertStringContainsString('const REMOTE_MENU_HEIGHT_CAP = 280;', $runtime);
        $this->assertGreaterThanOrEqual(2, substr_count($runtime, 'restoreCompactRecentPage()'));
        $this->assertSame(2, substr_count($runtime, "url.searchParams.set('per_page', String(q ? REMOTE_SEARCH_PAGE_SIZE : this.recentPageSize));"));
        $this->assertStringNotContainsString('recentPerPage', $runtime);
    }

    public function test_shared_dropdown_height_is_bounded_in_the_component_layer(): void
    {
        $css = file_get_contents(resource_path('css/components/list-filters.css'));
        $legacySharedCss = file_get_contents(resource_path('css/modules/application/10-shared-filters-order-overview.css'));

        $this->assertStringContainsString('--ft-remote-dropdown-max-height: 280px;', $css);
        $this->assertStringContainsString('max-height: min(var(--ft-remote-dropdown-max-height)', $css);
        $this->assertStringContainsString('max-height: min(var(--ft-remote-dropdown-max-height, 280px)', $legacySharedCss);
    }
}
