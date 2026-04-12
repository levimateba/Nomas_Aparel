<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $pricingTemplates = config('content_taxonomy.pricing_plan_templates', []);

        return view('admin.price.create', compact('pricingTemplates'));
    }

    public function store(Request $request)
    {
        $templateKeys = array_keys(config('content_taxonomy.pricing_plan_templates', []));

        $data = $request->validate([
            'plan_template' => ['required', Rule::in($templateKeys)],
            'custom_title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'display_price' => 'nullable|string|max:255',
            'billing_period' => 'required|string|max:100',
            'featured' => 'boolean',
            'features_text' => 'nullable|string',
        ]);

        $data['title'] = $this->resolvePlanTitle($data['plan_template'], $data['custom_title'] ?? null);

        $data['featured'] = $request->boolean('featured');
        
        // Parse features from text
        if (!empty($data['features_text'])) {
            $features = [];
            $lines = explode("\n", $data['features_text']);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $features[trim($key)] = trim($value);
                } else {
                    $features[] = $line;
                }
            }
            $data['features'] = $features;
        }
        unset($data['features_text']);
        unset($data['plan_template'], $data['custom_title']);

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
        $pricingTemplates = config('content_taxonomy.pricing_plan_templates', []);

        return view('admin.price.edit', compact('price', 'pricingTemplates'));
    }

    public function update(Request $request, string $id)
    {
        $price = Price::findOrFail($id);

        $templateKeys = array_keys(config('content_taxonomy.pricing_plan_templates', []));

        $data = $request->validate([
            'plan_template' => ['required', Rule::in($templateKeys)],
            'custom_title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'display_price' => 'nullable|string|max:255',
            'billing_period' => 'required|string|max:100',
            'featured' => 'boolean',
            'features_text' => 'nullable|string',
        ]);

        $data['title'] = $this->resolvePlanTitle($data['plan_template'], $data['custom_title'] ?? null);

        $data['featured'] = $request->boolean('featured');
        
        // Parse features from text
        if (!empty($data['features_text'])) {
            $features = [];
            $lines = explode("\n", $data['features_text']);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $features[trim($key)] = trim($value);
                } else {
                    $features[] = $line;
                }
            }
            $data['features'] = $features;
        }
        unset($data['features_text']);
        unset($data['plan_template'], $data['custom_title']);

        $price->update($data);

        return redirect()->route('admin.price.index')->with('success', 'Pricing item updated.');
    }

    public function destroy(string $id)
    {
        Price::findOrFail($id)->delete();
        return redirect()->route('admin.price.index')->with('success', 'Pricing item removed.');
    }

    private function resolvePlanTitle(string $templateKey, ?string $customTitle): string
    {
        $templates = config('content_taxonomy.pricing_plan_templates', []);

        if ($templateKey === 'custom') {
            return $customTitle ?: ($templates['custom']['title'] ?? 'Custom Plan');
        }

        return $templates[$templateKey]['title'] ?? ucfirst($templateKey) . ' Plan';
    }
}
