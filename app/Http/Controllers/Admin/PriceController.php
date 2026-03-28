<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Price;

class PriceController extends Controller
{
    public function index()
    {
        $prices = Price::latest()->paginate(10);
        return view('admin.price.index', compact('prices'));
    }

    public function create()
    {
        return view('admin.price.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'billing_period' => 'required|string|max:100',
            'featured' => 'boolean',
            'features_text' => 'nullable|string',
        ]);

        $data['featured'] = $request->boolean('featured');
        
        // Parse features from text
        if (!empty($data['features_text'])) {
            $features = [];
            $lines = explode("\n", $data['features_text']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $features[trim($key)] = trim($value);
                }
            }
            $data['features'] = $features;
        }
        unset($data['features_text']);

        Price::create($data);

        return redirect()->route('admin.price.index')->with('success', 'Pricing item created.');
    }

    public function show(string $id)
    {
        return redirect()->route('admin.price.index');
    }

    public function edit(string $id)
    {
        $price = Price::findOrFail($id);
        return view('admin.price.edit', compact('price'));
    }

    public function update(Request $request, string $id)
    {
        $price = Price::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'billing_period' => 'required|string|max:100',
            'featured' => 'boolean',
            'features_text' => 'nullable|string',
        ]);

        $data['featured'] = $request->boolean('featured');
        
        // Parse features from text
        if (!empty($data['features_text'])) {
            $features = [];
            $lines = explode("\n", $data['features_text']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $features[trim($key)] = trim($value);
                }
            }
            $data['features'] = $features;
        }
        unset($data['features_text']);

        $price->update($data);

        return redirect()->route('admin.price.index')->with('success', 'Pricing item updated.');
    }

    public function destroy(string $id)
    {
        Price::findOrFail($id)->delete();
        return redirect()->route('admin.price.index')->with('success', 'Pricing item removed.');
    }
}
