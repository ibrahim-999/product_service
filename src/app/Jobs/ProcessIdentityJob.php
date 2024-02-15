<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ProcessIdentityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $identity;

    /**
     * Create a new job instance.
     *
     * @param string $identity
     */
    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
        function handle()
        {

            $user = User::where('identity', $this->identity)->first();

            Log::info($user);
            if ($user) {
                $token = auth()->login($user);

                $connection = new AMQPStreamConnection(
                    env('RABBITMQ_HOST'),
                    env('RABBITMQ_PORT'),
                    env('RABBITMQ_LOGIN'),
                    env('RABBITMQ_PASSWORD'),
                    env('RABBITMQ_VHOST')
                );
                $channel = $connection->channel();
                $channel->exchange_declare('response_exchange', 'direct', false, true, false);
                $response = [
                    'user_id' => $user->id,
                    'token' => $token,
                ];
                $message = json_encode($response);
                $channel->basic_publish(
                    new AMQPMessage($message),
                    'response_exchange',
                    'response_routing_key'
                );
                $channel->close();
                $connection->close();
            }
        }
}
