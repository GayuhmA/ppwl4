<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_empty_list_and_create_form_are_available(): void
    {
        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Belum ada data project.')
            ->assertSee(route('projects.create'));

        $this->get(route('projects.create'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_project_can_be_created_and_success_message_is_temporary(): void
    {
        $data = ['title' => 'ABCDE', 'description' => '0123456789'];

        $this->post(route('projects.store'), $data)
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success', 'Project berhasil ditambahkan.');

        $this->assertDatabaseHas('projects', $data);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('ABCDE')
            ->assertSee('Project berhasil ditambahkan.');

        $this->get(route('projects.index'))
            ->assertDontSee('Project berhasil ditambahkan.');
    }

    public function test_detail_and_edit_form_display_the_project(): void
    {
        $project = Project::create([
            'title' => 'Project kuliah',
            'description' => 'Deskripsi project untuk praktikum.',
        ]);

        $this->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee($project->title)
            ->assertSee($project->description)
            ->assertSee(route('projects.edit', $project))
            ->assertSee('name="_method" value="DELETE"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('return confirm(', false);

        $this->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('value="Project kuliah"', false)
            ->assertSee($project->description)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_project_can_be_updated(): void
    {
        $project = Project::create([
            'title' => 'Project awal',
            'description' => 'Deskripsi project sebelum diedit.',
        ]);
        $data = ['title' => str_repeat('A', 200), 'description' => 'Deskripsi project sesudah diedit.'];

        $this->put(route('projects.update', $project), $data)
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success', 'Project berhasil diperbarui.');

        $this->assertDatabaseHas('projects', ['id' => $project->id, ...$data]);
        $this->get(route('projects.index'))->assertSee('Project berhasil diperbarui.');
    }

    #[DataProvider('invalidProjects')]
    public function test_invalid_data_is_rejected_when_creating_and_updating(array $data, array $errors): void
    {
        $original = ['title' => 'Project awal', 'description' => 'Deskripsi project sebelum diedit.'];
        $project = Project::create($original);

        $this->from(route('projects.create'))->post(route('projects.store'), $data)
            ->assertRedirect(route('projects.create'))
            ->assertSessionHasErrors($errors);

        $this->from(route('projects.edit', $project))->put(route('projects.update', $project), $data)
            ->assertRedirect(route('projects.edit', $project))
            ->assertSessionHasErrors($errors);

        $this->assertDatabaseCount('projects', 1);
        $this->assertDatabaseHas('projects', ['id' => $project->id, ...$original]);
    }

    public static function invalidProjects(): array
    {
        return [
            'required fields' => [['title' => '', 'description' => ''], ['title', 'description']],
            'short title' => [['title' => 'ABCD', 'description' => '0123456789'], ['title']],
            'short description' => [['title' => 'ABCDE', 'description' => '012345678'], ['description']],
            'long title' => [['title' => str_repeat('A', 201), 'description' => '0123456789'], ['title']],
            'title must be text' => [['title' => ['ABCDE'], 'description' => '0123456789'], ['title']],
            'description must be text' => [['title' => 'ABCDE', 'description' => ['0123456789']], ['description']],
        ];
    }

    public function test_create_form_keeps_input_and_displays_validation_errors(): void
    {
        $this->from(route('projects.create'))->post(route('projects.store'), [
            'title' => 'ABCD',
            'description' => 'Deskripsi tetap tersimpan di form.',
        ])->assertSessionHasErrors('title');

        $this->get(route('projects.create'))
            ->assertOk()
            ->assertSee('value="ABCD"', false)
            ->assertSee('Deskripsi tetap tersimpan di form.')
            ->assertSee('invalid-feedback');
    }

    public function test_edit_form_prioritizes_old_input_including_empty_values(): void
    {
        $project = Project::create([
            'title' => 'Judul dari database',
            'description' => 'Deskripsi asli dari database.',
        ]);

        $this->from(route('projects.edit', $project))->put(route('projects.update', $project), [
            'title' => 'Judul yang baru',
            'description' => '',
        ])->assertSessionHasErrors('description');

        $this->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('value="Judul yang baru"', false)
            ->assertDontSee('Judul dari database')
            ->assertDontSee('Deskripsi asli dari database.')
            ->assertSee('invalid-feedback');
    }

    public function test_project_can_only_be_deleted_through_the_delete_action(): void
    {
        $project = Project::create([
            'title' => 'Project kuliah',
            'description' => 'Deskripsi project untuk praktikum.',
        ]);

        $this->get(route('projects.show', $project))->assertOk();
        $this->get('/projects/'.$project->id.'/delete')->assertNotFound();
        $this->assertDatabaseHas('projects', ['id' => $project->id]);

        $this->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('success', 'Project berhasil dihapus.');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->get(route('projects.index'))->assertSee('Project berhasil dihapus.');
    }

    public function test_missing_projects_return_404(): void
    {
        $this->get('/projects/999')->assertNotFound();
        $this->get('/projects/999/edit')->assertNotFound();
        $this->put('/projects/999', [
            'title' => 'Project kuliah',
            'description' => 'Deskripsi project untuk praktikum.',
        ])->assertNotFound();
        $this->delete('/projects/999')->assertNotFound();
    }
}
