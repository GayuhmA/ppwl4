<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProjectTableSeeder;
use Database\Seeders\RandomProjectTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_seeders_can_run_separately_and_again_without_removing_existing_data(): void
    {
        $existing = Project::create([
            'title' => 'Project milik saya',
            'description' => 'Data yang sudah dibuat secara manual.',
        ]);

        $this->seed(ProjectTableSeeder::class);
        $this->assertDatabaseCount('projects', 11);

        $this->seed(RandomProjectTableSeeder::class);
        $this->assertDatabaseCount('projects', 36);

        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('projects', 36);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('projects', [
            'id' => $existing->id,
            'description' => 'Data yang sudah dibuat secara manual.',
        ]);
    }

    public function test_default_pagination_shows_ten_projects_with_bootstrap_navigation(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->total() === 35 && $projects->count() === 10
                && $projects->perPage() === 10 && $projects->lastPage() === 4)
            ->assertSee('Halaman: 1 dari 4')
            ->assertSee('Jumlah Data: 35')
            ->assertSee('Data Per Halaman: 10')
            ->assertSee('class="pagination"', false);
    }

    public function test_newest_projects_come_first_and_row_numbers_continue_on_page_two(): void
    {
        $this->travelTo(now()->startOfSecond());
        $this->seed(ProjectTableSeeder::class);

        $this->get(route('projects.index', ['page' => 2, 'per_page' => 5]))
            ->assertOk()
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->firstItem() === 6 && $projects->lastItem() === 10
                && $projects->pluck('title')->all() === [
                    'Project Judul Ke-5', 'Project Judul Ke-4', 'Project Judul Ke-3',
                    'Project Judul Ke-2', 'Project Judul Ke-1',
                ])
            ->assertSee('<td>6</td>', false)
            ->assertSee('<td>10</td>', false)
            ->assertDontSee('<td>1</td>', false);
    }

    public function test_creation_date_takes_priority_over_id_when_sorting(): void
    {
        $newer = Project::create(['title' => 'Project terbaru', 'description' => 'Project dengan tanggal terbaru.']);
        $older = Project::create(['title' => 'Project lama', 'description' => 'Project dengan tanggal lebih lama.']);
        $older->created_at = now()->subDay();
        $older->save();

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->pluck('id')->all() === [$newer->id, $older->id]);
    }

    public function test_search_and_page_size_are_kept_in_pagination_links(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get(route('projects.index', ['search' => 'Judul', 'per_page' => 5]));
        $response->assertOk()->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->total() === 10 && $projects->count() === 5);

        $nextUrl = $response->viewData('projects')->nextPageUrl();
        parse_str(parse_url($nextUrl, PHP_URL_QUERY), $parameters);
        $this->assertSame('Judul', $parameters['search']);
        $this->assertSame('5', $parameters['per_page']);
        $this->assertSame('2', $parameters['page']);

        $this->get($nextUrl)
            ->assertOk()
            ->assertViewHas('search', 'Judul')
            ->assertViewHas('perPage', 5)
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->total() === 10 && $projects->count() === 5
                && $projects->currentPage() === 2
                && $projects->every(fn (Project $project) => str_contains($project->title, 'Judul')))
            ->assertSee('value="Judul"', false)
            ->assertSee('<td>6</td>', false);
    }

    #[DataProvider('pageSizes')]
    public function test_supported_page_sizes_change_the_number_of_rows(int $size): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('projects.index', ['per_page' => $size]))
            ->assertOk()
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->count() === $size && $projects->perPage() === $size)
            ->assertSee('Data Per Halaman: '.$size);
    }

    public static function pageSizes(): array
    {
        return [[5], [10], [25]];
    }

    public function test_out_of_range_pages_and_empty_search_results_render_without_errors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get(route('projects.index', ['page' => 99]))
            ->assertOk()
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->count() === 0 && $projects->total() === 35)
            ->assertSee('Tidak ada data pada halaman ini.');

        $this->get(route('projects.index', ['search' => 'judul-yang-tidak-tersedia']))
            ->assertOk()
            ->assertViewHas('projects', fn (LengthAwarePaginator $projects) => $projects->total() === 0)
            ->assertSee('Tidak ada project yang cocok.');
    }

    public function test_unsupported_page_size_is_rejected(): void
    {
        $this->from(route('projects.index'))
            ->get(route('projects.index', ['per_page' => 7]))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHasErrors('per_page');
    }
}
