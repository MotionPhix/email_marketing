<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
  public function index()
  {
    $templates = EmailTemplate::query()
      ->where('team_id', Auth::user()->currentTeam->id)
      ->latest()
      ->get();

    return Inertia::render('Templates/Index', [
      'templates' => $templates,
      'categories' => EmailTemplate::CATEGORIES,
      'types' => EmailTemplate::TYPES,
    ]);
  }

  public function create()
  {
    return Inertia::render('Templates/Form', [
      'categories' => EmailTemplate::CATEGORIES,
      'types' => EmailTemplate::TYPES,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string|max:500',
      'subject' => 'required|string|max:255',
      'content' => 'required|string|min:50',
      'preview_text' => 'nullable|string|max:255',
      'category' => 'required|string|in:' . implode(',', EmailTemplate::CATEGORIES),
      'type' => 'required|string|in:' . implode(',', EmailTemplate::TYPES),
      'is_default' => 'boolean',
      'variables' => 'nullable|array',
      'design' => 'nullable|array',
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:50',
    ]);

    $template = new EmailTemplate($validated);
    $template->team_id = Auth::user()->currentTeam->id;
    $template->user_id = Auth::id();
    $template->save();

    return redirect()->route('templates.index')
      ->with('success', 'Template created successfully.');
  }

  public function edit(EmailTemplate $template)
  {
    $this->authorize('update', $template);

    return Inertia::render('Templates/Form', [
      'template' => $template,
      'categories' => EmailTemplate::CATEGORIES,
      'types' => EmailTemplate::TYPES,
    ]);
  }

  public function update(Request $request, EmailTemplate $template)
  {
    $this->authorize('update', $template);

    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string|max:500',
      'subject' => 'required|string|max:255',
      'content' => 'required|string|min:50',
      'preview_text' => 'nullable|string|max:255',
      'category' => 'required|string|in:' . implode(',', EmailTemplate::CATEGORIES),
      'type' => 'required|string|in:' . implode(',', EmailTemplate::TYPES),
      'is_default' => 'boolean',
      'variables' => 'nullable|array',
      'design' => 'nullable|array',
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:50',
    ]);

    $template->update($validated);

    return redirect()->route('templates.index')
      ->with('success', 'Template updated successfully.');
  }

  public function destroy(EmailTemplate $template)
  {
    $this->authorize('delete', $template);

    $template->delete();

    return redirect()->route('templates.index')
      ->with('success', 'Template deleted successfully.');
  }

  public function duplicate(EmailTemplate $template)
  {
    $this->authorize('create', EmailTemplate::class);

    $newTemplate = $template->replicate();
    $newTemplate->name = "{$template->name} (Copy)";
    $newTemplate->team_id = Auth::user()->currentTeam->id;
    $newTemplate->user_id = Auth::id();
    $newTemplate->save();

    return redirect()->route('templates.index')
      ->with('success', 'Template duplicated successfully.');
  }

  public function preview(EmailTemplate $template)
  {
    $this->authorize('view', $template);

    return Inertia::render('Templates/Preview', [
      'template' => $template
    ]);
  }

  public function variables()
  {
    return response()->json([
      'subscriber' => [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email Address',
        'custom_fields' => 'Custom Fields',
      ],
      'company' => [
        'name' => 'Company Name',
        'address' => 'Company Address',
      ],
      'campaign' => [
        'name' => 'Index Name',
        'subject' => 'Email Subject',
      ],
      'unsubscribe' => [
        'link' => 'Unsubscribe URL',
      ],
      'system' => [
        'current_date' => 'Current Date',
        'website_url' => 'Website URL',
      ],
    ]);
  }
}
