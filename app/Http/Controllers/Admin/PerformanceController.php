<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PerformanceController extends Controller
{
    public function index()
    {
        $reportPath = public_path('reports/performance.html');
        $lastRun = file_exists($reportPath) ? filemtime($reportPath) : null;
        
        return view('admin.performance.index', compact('lastRun'));
    }

    public function run(Request $request)
    {
        // Increase time limit for this request as Lighthouse takes ~30-60s
        set_time_limit(180);

        $request->validate([
            'url' => 'required|url',
            'strategy' => 'required|in:mobile,desktop',
        ]);

        $url = $request->input('url');
        $strategy = $request->input('strategy');
        $outputPath = public_path('reports/performance.html');
        
        // Ensure directory exists
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        // Build Command
        // 1. Basic flags
        $cmd = "npx lighthouse \"{$url}\" --output html --output-path \"{$outputPath}\" --chrome-flags=\"--headless\" --only-categories=performance,seo,accessibility,best-practices";
        
        // 2. Strategy (Device)
        $cmd .= " --emulated-form-factor={$strategy}";

        // 3. Fix CPU Warning (Optional: If local machine is slow, we can disable throttling to get 'machine' speed, 
        //    or keep it to simulate 'mobile' CPU. The warning is just a warning.)
        //    We'll add a flag to disable throttling if requested, but default to standard for accuracy.
        if ($request->has('disable_throttling')) {
            $cmd .= " --throttling.cpuSlowdownMultiplier=1";
        }

        // Execute
        // NOTE: Removed --view to prevent opening in new tab/browser window on server side
        $output = shell_exec($cmd . " 2>&1");
        
        if (!file_exists($outputPath)) {
            return back()->with('error', 'Lighthouse failed to generate report. Raw Output: ' . $output)->withInput();
        }

        return redirect()->route('admin.performance.index')->with('success', "Audit completed for {$url} ({$strategy})!")->withInput();
    }
}
