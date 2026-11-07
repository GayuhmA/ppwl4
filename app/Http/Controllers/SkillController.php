<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(): View
    {
        $skills = Skill::orderByDesc('level')->orderBy('name')->get();

        return view('skills.index', compact('skills'));
    }

    public function edit(Skill $skill): View
    {
        return view('skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:skills,name,'.$skill->id],
            'level' => ['required', 'integer', 'between:0,100'],
        ]);

        $skill->update($validated);

        return redirect()->route('skills.index')
            ->with('success', 'Skill berhasil diperbarui.');
    }
}
