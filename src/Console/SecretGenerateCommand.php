<?php declare(strict_types = 1);

namespace Middleware\Auth\Jwt\Console;

use Illuminate\Console\Command;

class SecretGenerateCommand extends Command
{
    protected $signature = 'jwt:generate';

    protected $description = 'Random secret generator command';

    public function handle(): void
    {
        $randomSecret = $this->generateRandomSecret();

        $this->info('Secret generated successfully.');
        $this->info('Update your environment variable with the following:');
        $this->line('JWT_SECRET=' . $randomSecret);
    }

    private function generateRandomSecret(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}
