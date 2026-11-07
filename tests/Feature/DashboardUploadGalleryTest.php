<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardUploadGalleryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->actingAs($this->admin);
        Storage::fake('public');
    }

    public function test_dashboard_displays_project_and_gallery_aggregates(): void
    {
        $web = Category::create(['name' => 'Web']);
        Category::create(['name' => 'Mobile']);
        Skill::create(['name' => 'Laravel', 'level' => 90]);
        Skill::create(['name' => 'JavaScript', 'level' => 75]);

        $withThumbnail = Project::create([
            'title' => 'Project dengan thumbnail',
            'description' => 'Deskripsi project dengan thumbnail.',
            'thumbnail' => 'projects/thumbnails/cover.jpg',
            'category_id' => $web->id,
            'status' => 'published',
        ]);
        Project::create([
            'title' => 'Project tanpa thumbnail',
            'description' => 'Deskripsi project tanpa thumbnail.',
            'category_id' => $web->id,
            'status' => 'draft',
        ]);
        $withThumbnail->images()->createMany([
            ['image' => 'projects/gallery/one.jpg', 'sort_order' => 1],
            ['image' => 'projects/gallery/two.jpg', 'sort_order' => 2],
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalProjects', 2)
            ->assertViewHas('publishedProjects', 1)
            ->assertViewHas('draftProjects', 1)
            ->assertViewHas('projectsWithThumbnail', 1)
            ->assertViewHas('totalGalleryImages', 2)
            ->assertViewHas('topSkill', fn (Skill $skill) => $skill->name === 'Laravel')
            ->assertViewHas('categories', fn ($categories) => $categories->firstWhere('name', 'Web')->projects_count === 2
                && $categories->firstWhere('name', 'Mobile')->projects_count === 0)
            ->assertSee('Project dengan thumbnail')
            ->assertSee('Published + Draft:')
            ->assertSee('Laravel (90%)')
            ->assertSee('2 gambar');
    }

    public function test_thumbnail_can_be_uploaded_replaced_and_removed_with_project(): void
    {
        $category = Category::create(['name' => 'Web']);

        $this->post(route('projects.store'), [
            'title' => 'Project upload',
            'description' => 'Deskripsi untuk project upload.',
            'category_id' => $category->id,
            'status' => 'published',
            'thumbnail' => $this->fakePng('cover.png', 600, 400),
        ])->assertRedirect(route('projects.index'));

        $project = Project::firstOrFail();
        $this->assertSame('published', $project->status);
        $this->assertSame($category->id, $project->category_id);
        $oldThumbnail = $project->thumbnail;
        Storage::disk('public')->assertExists($oldThumbnail);

        $this->put(route('projects.update', $project), [
            'title' => 'Project upload baru',
            'description' => 'Deskripsi untuk project upload baru.',
            'category_id' => $category->id,
            'status' => 'draft',
            'thumbnail' => $this->fakePng('replacement.png', 800, 600),
        ])->assertRedirect(route('projects.index'));

        $project->refresh();
        Storage::disk('public')->assertMissing($oldThumbnail);
        Storage::disk('public')->assertExists($project->thumbnail);

        $currentThumbnail = $project->thumbnail;
        $this->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        Storage::disk('public')->assertMissing($currentThumbnail);
    }

    public function test_multiple_gallery_images_can_be_uploaded_and_deleted(): void
    {
        $project = Project::create([
            'title' => 'Project gallery',
            'description' => 'Deskripsi untuk project gallery.',
        ]);

        $this->from(route('projects.show', $project))
            ->post(route('projects.gallery.store', $project), [
                'images' => [
                    $this->fakePng('one.png', 900, 600),
                    $this->fakePng('two.png', 900, 600),
                ],
            ])
            ->assertRedirect(route('projects.show', $project))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('project_images', 2);
        $images = $project->images()->get();
        $this->assertSame([1, 2], $images->pluck('sort_order')->all());
        Storage::disk('public')->assertExists($images[0]->image);
        Storage::disk('public')->assertExists($images[1]->image);

        $this->from(route('projects.show', $project))
            ->put(route('projects.gallery.update', [$project, $images[0]]), [
                'caption' => 'Tampilan halaman utama',
            ])
            ->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('project_images', [
            'id' => $images[0]->id,
            'caption' => 'Tampilan halaman utama',
        ]);

        $deletedPath = $images[0]->image;
        $this->from(route('projects.show', $project))
            ->delete(route('projects.gallery.destroy', [$project, $images[0]]))
            ->assertRedirect(route('projects.show', $project));

        Storage::disk('public')->assertMissing($deletedPath);
        $this->assertDatabaseCount('project_images', 1);
    }

    public function test_gallery_is_limited_to_ten_images_and_ownership_is_checked(): void
    {
        $project = Project::create([
            'title' => 'Project pertama',
            'description' => 'Deskripsi untuk project pertama.',
        ]);
        $otherProject = Project::create([
            'title' => 'Project kedua',
            'description' => 'Deskripsi untuk project kedua.',
        ]);

        foreach (range(1, 9) as $order) {
            $project->images()->create([
                'image' => "projects/gallery/{$order}.jpg",
                'sort_order' => $order,
            ]);
        }

        $this->from(route('projects.show', $project))
            ->post(route('projects.gallery.store', $project), [
                'images' => [$this->fakePng('too-small.png', 500, 500)],
            ])
            ->assertRedirect(route('projects.show', $project))
            ->assertSessionHasErrors('images.0');

        $this->from(route('projects.show', $project))
            ->post(route('projects.gallery.store', $project), [
                'images' => [
                    $this->fakePng('ten.png'),
                    $this->fakePng('eleven.png'),
                ],
            ])
            ->assertRedirect(route('projects.show', $project))
            ->assertSessionHasErrors('images');

        $this->assertDatabaseCount('project_images', 9);

        $foreignImage = ProjectImage::create([
            'project_id' => $otherProject->id,
            'image' => 'projects/gallery/foreign.jpg',
        ]);

        $this->delete(route('projects.gallery.destroy', [$project, $foreignImage]))
            ->assertNotFound();
        $this->put(route('projects.gallery.update', [$project, $foreignImage]), [
            'caption' => 'Tidak boleh diubah',
        ])->assertNotFound();
        $this->assertDatabaseHas('project_images', ['id' => $foreignImage->id]);
    }

    public function test_public_portfolio_displays_thumbnail_and_gallery(): void
    {
        $project = Project::create([
            'title' => 'Project publik',
            'description' => 'Deskripsi project yang tampil untuk publik.',
            'thumbnail' => 'projects/thumbnails/public.jpg',
        ]);
        $project->images()->create([
            'image' => 'projects/gallery/public-gallery.jpg',
            'caption' => 'Tampilan utama',
        ]);

        auth()->logout();

        $this->get(route('portfolio.projects'))
            ->assertOk()
            ->assertSee('Project publik')
            ->assertSee('storage/projects/thumbnails/public.jpg');

        $this->get(route('portfolio.projects.show', $project))
            ->assertOk()
            ->assertSee('storage/projects/gallery/public-gallery.jpg')
            ->assertSee('Tampilan utama');
    }

    private function fakePng(string $name, int $width = 800, int $height = 600): UploadedFile
    {
        $row = "\x00".str_repeat("\x22\x66\xaa", $width);
        $pixels = str_repeat($row, $height);
        $header = pack('NNC5', $width, $height, 8, 2, 0, 0, 0);

        $png = "\x89PNG\r\n\x1a\n"
            .$this->pngChunk('IHDR', $header)
            .$this->pngChunk('IDAT', gzcompress($pixels))
            .$this->pngChunk('IEND', '');

        return UploadedFile::fake()->createWithContent($name, $png);
    }

    private function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }
}
