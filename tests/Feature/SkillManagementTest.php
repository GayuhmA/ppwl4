<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs(User::factory()->create(['email' => 'admin@example.com']));
    }

    public function test_admin_can_open_and_update_a_skill(): void
    {
        $skill = Skill::create(['name' => 'Laravel', 'level' => 70]);

        $this->get(route('skills.index'))
            ->assertOk()
            ->assertSee('Laravel')
            ->assertSee('70%');

        $this->get(route('skills.edit', $skill))
            ->assertOk()
            ->assertSee('value="Laravel"', false)
            ->assertSee('value="70"', false);

        $this->put(route('skills.update', $skill), [
            'name' => 'Laravel Framework',
            'level' => 95,
        ])->assertRedirect(route('skills.index'))
            ->assertSessionHas('success', 'Skill berhasil diperbarui.');

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'name' => 'Laravel Framework',
            'level' => 95,
        ]);
    }

    public function test_skill_update_validates_name_uniqueness_and_level_range(): void
    {
        Skill::create(['name' => 'PHP', 'level' => 80]);
        $skill = Skill::create(['name' => 'Laravel', 'level' => 70]);

        $this->from(route('skills.edit', $skill))
            ->put(route('skills.update', $skill), [
                'name' => 'PHP',
                'level' => 101,
            ])
            ->assertRedirect(route('skills.edit', $skill))
            ->assertSessionHasErrors(['name', 'level']);

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'name' => 'Laravel',
            'level' => 70,
        ]);
    }

    public function test_non_admin_cannot_manage_skills(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'member@example.com']));

        $this->get(route('skills.index'))->assertForbidden();
    }
}
