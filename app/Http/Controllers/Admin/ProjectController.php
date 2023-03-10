<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;



class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $project = new Project();
        return view('admin.projects.create', compact('project'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|unique:projects|min:2|max:100',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg, jpg, png',
            'github' => 'nullable|url',

        ], [
            'title.required' => 'Il titolo è necessario',
            'title.string' => 'Il titolo deve essere una stringa',
            'title.unique' => 'Il titolo non può essere lo stesso di un altro progetto',
            'title.min' => 'Il titolo è necessario sia di almeno 2 lettere',
            'title.max' => 'Il titolo è necessario sia meno di almeno 100 lettere',
            'description.required' => 'Il paragrafo deve essere inserito',
            'description.string' => 'Il paragraph deve essere una stringa',
            'image.image' => 'L\' immagine deve essere un file immagine',
            'image.mimes' => 'L\' immagine deve avere come estensioni jpeg, jpg, png',
            'github.url' => 'Il link github deve essere corretto',

        ]);
        $data = $request->all();
        $project = new Project();
        if (array_key_exists('image', $data)) {
            $img_url = Storage::put('projects', $data['image']);
            $data['image'] = $img_url;
        }
        $project->fill($data);

        $project->slug = Str::slug($project->title, '-');
        $project->save();
        return to_route('admin.projects.show', $project->id)->with('type', 'success')->with('msg', "Il project '$project->title' è stato creato con successo.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => ['required', 'string', Rule::unique('projects')->ignore($project->id), 'min:2', 'max:100'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image|mimes:jpeg, jpg, png'],
            'github' => ['nullable', 'url']

        ], [
            'title.required' => 'Il titolo è necessario',
            'title.string' => 'Il titolo deve essere una stringa',
            'title.unique' => 'Il titolo non può essere lo stesso di un altro progetto',
            'title.min' => 'Il titolo è necessario sia di almeno 2 lettere',
            'title.max' => 'Il titolo è necessario sia meno di almeno 100 lettere',
            'description.required' => 'La descrizione deve essere inserita',
            'description.string' => 'La descrizione deve essere una stringa',
            'image.image' => 'L\' immagine deve essere un file immagine',
            'image.mimes' => 'L\' immagine deve avere come estensioni jpeg, jpg, png',
            'github.url' => 'Il link github deve essere corretto',

        ]);
        $data = $request->all();
        $project['slug'] = Str::slug($data['title'], '-');
        if (array_key_exists('image', $data)) {
            if ($project->image) Storage::delete($project->image);
            $img_url = Storage::put('projects', $data['image']);
            $data['image'] = $img_url;
        }
        $project->update($data);
        return to_route('admin.projects.show', $project->id)->with('type', 'success')->with('msg', "Il project '$project->title' è stato aggiornato con successo.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if ($project->image) Storage::delete($project->image);
        $project->delete();
        return to_route('admin.projects.index')->with('type', 'danger')->with('msg', "Il project '$project->title' è stato cancellato con successo.");
    }
}
