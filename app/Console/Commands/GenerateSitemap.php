<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\News;
use App\Models\Video;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seo:generate-sitemap';

    /**
     * The console command description.
     */
    protected $description = 'Generate the sitemap.xml file for SEO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Homepage
        $sitemap->add(
            Url::create('/')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        // Products listing
        $sitemap->add(
            Url::create('/products')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );

        // Individual products
        Product::all()->each(function (Product $product) use ($sitemap) {
            $sitemap->add(
                Url::create("/products/{$product->id}-" . ($product->slug ?? 'product'))
                    ->setLastModificationDate($product->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });

        // News listing
        $sitemap->add(
            Url::create('/news')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.8)
        );

        // Individual news articles
        News::published()->get()->each(function (News $news) use ($sitemap) {
            $sitemap->add(
                Url::create("/news/{$news->id}-" . ($news->slug ?? 'news'))
                    ->setLastModificationDate($news->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7)
            );
        });

        // Video listing
        $sitemap->add(
            Url::create('/videos')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7)
        );

        // Individual videos
        Video::active()->ordered()->get()->each(function (Video $video) use ($sitemap) {
            $sitemap->add(
                Url::create("/videos/{$video->id}-" . ($video->slug ?: 'video'))
                    ->setLastModificationDate($video->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.6)
            );
        });

        // Contact page
        $sitemap->add(
            Url::create('/contact')
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6)
        );

        // Save sitemap
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at public/sitemap.xml');

        return Command::SUCCESS;
    }
}
