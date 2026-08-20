<?php

namespace App\Models\Frontend\President;

use CodeIgniter\Model;

/**
 * Sultangazi Belediyesi ana sitesinden senkron edilen başkan içerikleri.
 *
 * Veriler `_tools/sync_president.php` ile (cron, günde iki kez) tazelenir.
 * Sayfalar bu tabloyu okur; ziyaretçi isteğinde dış servise gidilmez.
 */
class SultangaziPresidentModel extends Model
{
    protected $table = 'sultangazi_president_contents';

    /**
     * Tek içerik (özgeçmiş, mesaj).
     */
    public function content(string $slug, int $id): ?object
    {
        return $this->db->table($this->table)
            ->where('content_id', $id)
            ->where('slug', $slug)
            ->limit(1)
            ->get()->getRow();
    }

    /**
     * Tüm içerikler (sıralı).
     */
    public function contents(): array
    {
        return $this->db->table($this->table)
            ->orderBy('sort_order', 'ASC')
            ->get()->getResult();
    }

    /**
     * Başkan genel bilgileri.
     */
    public function info(): ?object
    {
        return $this->db->table('sultangazi_president_info')->limit(1)->get()->getRow();
    }

    /**
     * Son senkron zamanı.
     */
    public function lastSyncedAt(): ?string
    {
        $row = $this->db->table($this->table)->selectMax('synced_at', 'son')->get()->getRow();

        return $row->son ?? NULL;
    }
}
