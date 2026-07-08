<?php

namespace App\Http\Controllers;

use App\Models\ScanFaq;
use App\Models\ScanHelpVideo;
use Illuminate\Http\Request;

class ScanHelpController extends Controller
{
    public function index()
    {
        $faqs = ScanFaq::ordered()->get();
        $videos = ScanHelpVideo::ordered()->get();

        return view('scan_help.index', compact('faqs', 'videos'));
    }

    public function storeFaq(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        ScanFaq::create($data);

        return back()->with('success', 'FAQ added.');
    }

    public function updateFaq(Request $request, ScanFaq $scanFaq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $scanFaq->update($data);

        return back()->with('success', 'FAQ updated.');
    }

    public function destroyFaq(ScanFaq $scanFaq)
    {
        $scanFaq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    public function storeVideo(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url|max:500',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        ScanHelpVideo::create($data);

        return back()->with('success', 'Help video added.');
    }

    public function updateVideo(Request $request, ScanHelpVideo $scanHelpVideo)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'video_url' => 'required|url|max:500',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $scanHelpVideo->update($data);

        return back()->with('success', 'Help video updated.');
    }

    public function destroyVideo(ScanHelpVideo $scanHelpVideo)
    {
        $scanHelpVideo->delete();

        return back()->with('success', 'Help video deleted.');
    }
}
