<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductQuestion::query()->with('product')->latest();
        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'answered') {
                $query->whereNotNull('answer');
            } elseif ($status === 'unanswered') {
                $query->whereNull('answer');
            }
        }
        $questions = $query->paginate(20)->withQueryString();
        $stats = [
            'total' => ProductQuestion::count(),
            'unanswered' => ProductQuestion::whereNull('answer')->count(),
            'answered' => ProductQuestion::whereNotNull('answer')->count(),
        ];

        return view('admin.questions.index', compact('questions', 'stats'));
    }

    public function update(Request $request, ProductQuestion $question)
    {
        $data = $request->validate([
            'answer' => 'nullable|string|max:2000',
            'approved' => 'nullable|boolean',
        ]);

        $hadAnswer = !empty($question->answer);
        $question->update([
            'answer' => $data['answer'] ?? null,
            'approved' => $request->boolean('approved'),
            'answered_at' => !empty($data['answer']) ? now() : null,
        ]);

        if (!$hadAnswer && !empty($data['answer']) && $question->approved) {
            try {
                Mail::html(
                    'Your question on <strong>' . e($question->product?->name ?? 'our product') . '</strong> has been answered: ' . e($data['answer']),
                    fn ($message) => $message->to($question->email)->subject('Your Question Was Answered')
                );
            } catch (\Throwable $exception) {
            }
        }

        return back()->with('success', 'Question updated.');
    }

    public function destroy(ProductQuestion $question)
    {
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }
}
