<?php

declare(strict_types=1);

namespace App\Models;

class HutangPiutangHistory extends Model
{
    protected string $table = 'hutang_piutang_history';

    public function findByHutangPiutang(int $hutangPiutangId): array
    {
        return $this->findAll(['hutang_piutang_id' => $hutangPiutangId], 'tanggal DESC');
    }
}
