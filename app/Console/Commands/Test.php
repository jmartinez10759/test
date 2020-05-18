<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Http\Response;

/**
 * Class Test
 * @package App\Console\Commands
 */
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:api {--request=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build one command for request of api';
    /**
     * @var Client
     */
    private $_client;
    /**
     * Create a new command instance.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->_client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mail = $this->ask('What is your mail?');
        if ($mail != 'demo@demo.com'){
            $this->error("I did not enter the correct email, please contact an administrator");
            return ;
        }
        $requestCount = (isset($this->option('request')[0]))?$this->option('request')[0]: 1 ;
        $error = $success = 0;
        $this->info("Iniciando la conexion del endpoint: ".env('API_ENDPOINT')." ...");

        for ($i = 0; $i < $requestCount; $i++){
            try {
                $response = $this->_client->request('POST',env('API_ENDPOINT'));
                if ($response->getStatusCode() == 200){
                    $this->line($response->getBody());
                    $success++;
                }
            } catch ( \Exception $e) {
                if(in_array($e->getCode(),array_keys($this->_getStatusMessage()))){
                    $error++;
                    \Log::error($e->getMessage()."Conexiones Fallidas: {$i}");
                }
            }
        }

        $this->info("El servicio que intenta consumir no esta disponible, errores encontrados: {$error}");
        $this->info("Conexiones exitosa: {$success}");
    }

    /**
     * This method is use for validate state code
     * @return string[]
     */
    private function _getStatusMessage() {
        return [
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            412 => 'Precondition Failed',
            500 => 'Internal Server Error'
        ];
    }
}
