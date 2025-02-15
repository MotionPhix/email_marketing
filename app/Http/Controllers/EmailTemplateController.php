<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
  public function index()
  {
    $templates = EmailTemplate::latest()->get();

    return Inertia::render('Templates/Index', [
      'templates' => $templates
    ]);
  }

  public function create()
  {
    return Inertia::render('Templates/Form');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'subject' => 'required|string|max:255',
      'content' => 'required|string',
      'preview_text' => 'nullable|string|max:255',
      'category' => 'required|string|in:newsletter,promotional,transactional',
      'variables' => 'nullable|array',
    ]);

    EmailTemplate::create($validated);

    return redirect()->route('templates.index')
      ->with('success', 'Template created successfully.');
  }

  public function edit(EmailTemplate $template)
  {
    return Inertia::render('Templates/Form', [
      'template' => $template
    ]);
  }

  public function update(Request $request, EmailTemplate $template)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'subject' => 'required|string|max:255',
      'content' => 'required|string',
      'preview_text' => 'nullable|string|max:255',
      'category' => 'required|string|in:newsletter,promotional,transactional',
      'variables' => 'nullable|array',
    ]);

    $template->update($validated);

    return redirect()->route('templates.index')
      ->with('success', 'Template updated successfully.');
  }

  public function destroy(EmailTemplate $template)
  {
    $template->delete();

    return redirect()->route('templates.index')
      ->with('success', 'Template deleted successfully.');
  }

  public function duplicate(EmailTemplate $template)
  {
    $newTemplate = $template->replicate();
    $newTemplate->name = "{$template->name} (Copy)";
    $newTemplate->save();

    return redirect()->route('templates.index')
      ->with('success', 'Template duplicated successfully.');
  }

  public function preview(EmailTemplate $template)
  {
    return Inertia::render('Templates/Preview', [
      'template' => $template
    ]);
  }

  public function variables()
  {
    return response()->json([
      'user' => [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email Address',
        'company' => 'Company Name',
      ],
      'campaign' => [
        'name' => 'Campaign Name',
        'subject' => 'Email Subject',
        'unsubscribe_url' => 'Unsubscribe URL',
      ],
      'custom' => [
        'current_date' => 'Current Date',
        'website_url' => 'Website URL',
      ],
    ]);
  }
}
