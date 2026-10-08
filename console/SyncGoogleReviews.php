<?php namespace CRSCompany\FrameworC\Console;

use CRSCompany\FrameworC\Classes\GoogleReviews;
use Illuminate\Console\Command;

/**
 * SyncGoogleReviews downloads Google reviews for every GooglePlace entry.
 *
 * Runs daily via Plugin::registerSchedule(); run it by hand after adding a place.
 *
 * @link https://docs.octobercms.com/4.x/extend/console-commands.html
 */
class SyncGoogleReviews extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'frameworc:google-reviews';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Download Google reviews for every place in Builder → Google recenze';

    /**
     * handle executes the console command.
     */
    public function handle()
    {
        $results = GoogleReviews::syncAll();

        if (!$results) {
            $this->info('No GooglePlace entries to sync.');
            return 0;
        }

        $failed = 0;
        foreach ($results as $title => $error) {
            if ($error === null) {
                $this->info("{$title}: synced");
            }
            else {
                $this->error("{$title}: {$error}");
                $failed++;
            }
        }

        return $failed ? 1 : 0;
    }
}
