<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AktivitasKonsultasi implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $userId;
    public ?int $konsultasiId;
    public string $tipe;

    public function __construct(int $userId, ?int $konsultasiId = null, string $tipe = 'umum')
    {
        $this->userId = $userId;
        $this->konsultasiId = $konsultasiId;
        $this->tipe = $tipe;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'AktivitasKonsultasi';
    }

    public function broadcastWith(): array
    {
        return [
            'konsultasi_id' => $this->konsultasiId,
            'tipe' => $this->tipe,
        ];
    }
}