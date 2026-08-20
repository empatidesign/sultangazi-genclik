<?php

namespace App\Models\Frontend\Events;

use CodeIgniter\Model;

/**
 * Nexora'dan senkron edilen etkinlikler.
 *
 * Veriler `_tools/sync_events.php` ile (cron, günde iki kez) tazelenir.
 * Sorgular her zaman geçmiş etkinlikleri hariç tutar: bitiş tarihi
 * bugünden önce olan kayıt listelenmez.
 */
class NexoraEventsModel extends Model
{
    protected $table = 'nexora_events';

    /**
     * Yaklaşan etkinlikler.
     */
    public function upcoming(?int $limit = NULL, int $offset = 0): array
    {
        $query = $this->db->table($this->table);
        $query->where('end_date >=', date('Y-m-d'));
        $query->orderBy('start_date', 'ASC');
        $query->orderBy('start_time', 'ASC');

        if ($limit !== NULL) {
            $query->limit($limit, $offset);
        }

        return $query->get()->getResult();
    }

    /**
     * Yaklaşan etkinlik sayısı (sayfalama için).
     */
    public function upcomingCount(): int
    {
        return $this->db->table($this->table)
            ->where('end_date >=', date('Y-m-d'))
            ->countAllResults();
    }

    /**
     * Tek etkinlik. Geçmiş etkinlikler açılmaz.
     */
    public function findBySlug(string $slug, int $id): ?object
    {
        $query = $this->db->table($this->table);
        $query->where('event_id', $id);
        $query->where('slug', $slug);
        $query->where('end_date >=', date('Y-m-d'));
        $query->limit(1);

        return $query->get()->getRow();
    }

    /**
     * Aynı kategoriden diğer etkinlikler (detay sayfası için).
     */
    public function related(?string $category, int $exceptId, int $limit = 3): array
    {
        $query = $this->db->table($this->table);
        $query->where('end_date >=', date('Y-m-d'));
        $query->where('event_id !=', $exceptId);

        if (isNotNull($category)) {
            $query->where('category_name', $category);
        }

        $query->orderBy('start_date', 'ASC');
        $query->limit($limit);

        return $query->get()->getResult();
    }

    /**
     * Son senkron zamanı (yönetim/izleme için).
     */
    public function lastSyncedAt(): ?string
    {
        $row = $this->db->table($this->table)
            ->selectMax('synced_at', 'son')
            ->get()->getRow();

        return $row->son ?? NULL;
    }
}
