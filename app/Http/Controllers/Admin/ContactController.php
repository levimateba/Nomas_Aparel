<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(10);
        return view('admin.contact.index', compact('contacts'));
    }

    public function create()
    {
        return view('admin.contact.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'type' => 'required|string|max:50',
        ]);

        Contact::create($data);
        return redirect()->route('admin.contact.index')->with('success', 'Contact added.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.contact.index');
    }

    public function edit(string $id)
    {
        $contact = Contact::findOrFail($id);
        return view('admin.contact.edit', compact('contact'));
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'type' => 'required|string|max:50',
        ]);

        $contact = Contact::findOrFail($id);
        $contact->update($data);

        return redirect()->route('admin.contact.index')->with('success', 'Contact updated.');
    }

    public function destroy(string $id)
    {
        Contact::findOrFail($id)->delete();
        return redirect()->route('admin.contact.index')->with('success', 'Contact removed.');
    }
}
