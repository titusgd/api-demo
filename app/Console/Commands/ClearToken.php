<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Token;
use App\Models\TokenLog;

class ClearToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearToken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $time = date('Y-m-d H:i:s');
            $token = Token::where('expired', '<=', $time)->get();
            foreach($token as $key => $value){

                // 刪除過期 token
                $token_del = Token::find($value['id']);
                $token_del->delete();

                // 刪除 logs
                TokenLog::where('token', $value['token'])->delete();
            }
            Log::info('=== clear expired token ===');
        } catch (\Exception $e) {
            $message = mb_convert_encoding($e->getMessage(), 'utf-8', 'auto');
            Log::error($message);
        }

    }
}
