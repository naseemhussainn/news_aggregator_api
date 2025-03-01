<?php

namespace App\Console\Commands;

use App\Services\Guardian\GuardianService;
use App\Services\NewsAPI\NewsAPIService;
use App\Services\NYTimes\NYTimesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from various sources';

    /**
     * Execute the console command.
     */
    public function handle(
        NewsAPIService $newsAPIService,
        GuardianService $guardianService, 
        NYTimesService $nyTimesService
    ) {
        $source = $this->argument('source');
        
        try {
            if ($source === 'newsapi' || $source === null) {
                $this->info('Fetching articles from NewsAPI...');
                $articles = $newsAPIService->fetchArticles();
                $this->info('Saved ' . count($articles) . ' articles from NewsAPI');
            }
            
            if ($source === 'guardian' || $source === null) {
                $this->info('Fetching articles from The Guardian...');
                $articles = $guardianService->fetchArticles();
                $this->info('Saved ' . count($articles) . ' articles from The Guardian');
            }
            
            if ($source === 'nytimes' || $source === null) {
                $this->info('Fetching articles from New York Times...');
                $articles = $nyTimesService->fetchArticles();
                $this->info('Saved ' . count($articles) . ' articles from New York Times');
            }
            
            $this->info('News fetch completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error fetching news: ' . $e->getMessage());
            Log::error('News fetch command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}