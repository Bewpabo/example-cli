<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Http;
use DB;
use Exception;

class InitialCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init:countries';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Initial countries data for first time.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $url = "https://restcountries.eu/rest/v2/all";
            $this->info("Initial......");
            $response = Http::get($url);
            if ($response->getStatusCode() != 200) {
                return false;
            }

            $this->output->progressStart(count($response->json()));

            DB::beginTransaction();

            foreach ($response->json() as $data) {
                DB::table('countries')->insert([
                    'name' => $data['name'],
                    'region' => $data['region'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                $this->output->progressAdvance();
            }

            DB::commit();

            $this->output->progressFinish();

            $this->info("Finish");
        } catch (Exception $e) {
            DB::rollback();
            $this->error($e->getMessage());
        }

    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
