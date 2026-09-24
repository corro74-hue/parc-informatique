<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Equipment;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use PDO;

final class EquipmentRepository implements EquipmentRepositoryInterface
{
    private PDO $db;

    /**
     * Sélection standard avec toutes les jointures pour affichage
     */
    private const BASE_SELECT = '
        SELECT e.*,
               c.name  AS category_name,
               c.code  AS category_code,
               b.name  AS brand_name,
               s.name  AS status_name,
               s.code  AS status_code,
               s.color AS status_color,
               sv.name AS service_name,
               st.name AS site_name,
               l.full_name AS location_name,
               CONCAT(emp.first_name, " ", emp.last_name) AS responsible_name
        FROM equipment e
        LEFT JOIN equipment_categories c  ON c.id = e.category_id
        LEFT JOIN brands b                ON b.id = e.brand_id
        LEFT JOIN equipment_statuses s    ON s.id = e.status_id
        LEFT JOIN services sv             ON sv.id = e.service_id
        LEFT JOIN sites st                ON st.id = e.site_id
        LEFT JOIN locations l             ON l.id = e.location_id
        LEFT JOIN employees emp           ON emp.id = e.responsible_id
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function findById(int $id): ?Equipment
    {
        $sql = self::BASE_SELECT . ' WHERE e.id = :id AND e.deleted_at IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Equipment::fromArray($row) : null;
    }

    public function findByInventoryNumber(string $inventoryNumber): ?Equipment
    {
        $sql = self::BASE_SELECT . ' WHERE e.inventory_number = :n AND e.deleted_at IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['n' => $inventoryNumber]);
        $row = $stmt->fetch();

        return $row ? Equipment::fromArray($row) : null;
    }

    public function findBySerialNumber(string $serialNumber): ?Equipment
    {
        $sql = self::BASE_SELECT . ' WHERE e.serial_number = :s AND e.deleted_at IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['s' => $serialNumber]);
        $row = $stmt->fetch();

        return $row ? Equipment::fromArray($row) : null;
    }

    /**
     * Recherche globale rapide — utilisée par la barre de recherche du header.
     * Renvoie max $limit équipements triés par pertinence.
     *
     * @return Equipment[]
     */
    public function searchGlobal(string $term, int $limit = 10): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $sql = self::BASE_SELECT . '
            WHERE e.deleted_at IS NULL
              AND (
                    e.inventory_number LIKE :s1
                 OR e.designation      LIKE :s2
                 OR e.serial_number    LIKE :s3
                 OR e.model_text       LIKE :s4
                 OR b.name             LIKE :s5
              )
            ORDER BY
                CASE
                    WHEN e.inventory_number LIKE :s6 THEN 1
                    WHEN e.designation      LIKE :s7 THEN 2
                    ELSE 3
                END,
                e.inventory_number ASC
            LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $like = '%' . $term . '%';
        $stmt->bindValue(':s1', $like);
        $stmt->bindValue(':s2', $like);
        $stmt->bindValue(':s3', $like);
        $stmt->bindValue(':s4', $like);
        $stmt->bindValue(':s5', $like);
        $stmt->bindValue(':s6', $term . '%');   // pertinence : commence par le terme
        $stmt->bindValue(':s7', $term . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Equipment::fromArray($r), $stmt->fetchAll());
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 15, string $sortBy = 'created_at', string $sortDir = 'DESC'): array
    {
        // Sécuriser le tri
        $allowedSorts = ['created_at', 'updated_at', 'inventory_number', 'designation', 'acquisition_date', 'acquisition_value'];
        $sortBy = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        // Construire les conditions WHERE
        $where = ['e.deleted_at IS NULL'];
        $params = [];

        // ----- Recherche : utiliser des placeholders distincts -----
        // (PDO avec EMULATE_PREPARES=false n'accepte pas un même placeholder plusieurs fois)
        if (!empty($filters['search'])) {
            $where[] = '(e.inventory_number LIKE :search1
                        OR e.designation LIKE :search2
                        OR e.serial_number LIKE :search3
                        OR e.model_text LIKE :search4
                        OR b.name LIKE :search5)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params['search1'] = $searchTerm;
            $params['search2'] = $searchTerm;
            $params['search3'] = $searchTerm;
            $params['search4'] = $searchTerm;
            $params['search5'] = $searchTerm;
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'e.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['status_id'])) {
            $where[] = 'e.status_id = :status_id';
            $params['status_id'] = (int) $filters['status_id'];
        }

        if (!empty($filters['service_id'])) {
            $where[] = 'e.service_id = :service_id';
            $params['service_id'] = (int) $filters['service_id'];
        }

        if (!empty($filters['site_id'])) {
            $where[] = 'e.site_id = :site_id';
            $params['site_id'] = (int) $filters['site_id'];
        }

        if (!empty($filters['brand_id'])) {
            $where[] = 'e.brand_id = :brand_id';
            $params['brand_id'] = (int) $filters['brand_id'];
        }

        // ----- Filtres avancés -----
        if (!empty($filters['date_from'])) {
            $where[] = 'e.acquisition_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'e.acquisition_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['value_min'])) {
            $where[] = 'e.acquisition_value >= :value_min';
            $params['value_min'] = (float) $filters['value_min'];
        }

        if (!empty($filters['value_max'])) {
            $where[] = 'e.acquisition_value <= :value_max';
            $params['value_max'] = (float) $filters['value_max'];
        }

        if (!empty($filters['under_warranty'])) {
            $where[] = 'e.warranty_end_date IS NOT NULL AND e.warranty_end_date >= CURDATE()';
        }

        if (!empty($filters['without_serial'])) {
            $where[] = '(e.serial_number IS NULL OR e.serial_number = "")';
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // ----- Compter le total -----
        $countSql = 'SELECT COUNT(DISTINCT e.id) FROM equipment e
                     LEFT JOIN brands b ON b.id = e.brand_id' . $whereSql;
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        // ----- Calculer la pagination -----
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        // ----- Récupérer les données -----
        $sql = self::BASE_SELECT . $whereSql . " ORDER BY e.$sortBy $sortDir LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'data'      => array_map(fn($r) => Equipment::fromArray($r), $rows),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    // ============================================
    // EXPORT (sans pagination)
    // ============================================

    /**
     * Récupère TOUS les équipements correspondant aux filtres,
     * sans pagination — utilisé pour les exports Excel / CSV / PDF.
     *
     * @return Equipment[]
     */
    public function findAllForExport(array $filters = []): array
    {
        $where  = ['e.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(e.inventory_number LIKE :search1
                        OR e.designation LIKE :search2
                        OR e.serial_number LIKE :search3
                        OR e.model_text LIKE :search4
                        OR b.name LIKE :search5)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params['search1'] = $searchTerm;
            $params['search2'] = $searchTerm;
            $params['search3'] = $searchTerm;
            $params['search4'] = $searchTerm;
            $params['search5'] = $searchTerm;
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'e.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['status_id'])) {
            $where[] = 'e.status_id = :status_id';
            $params['status_id'] = (int) $filters['status_id'];
        }

        if (!empty($filters['service_id'])) {
            $where[] = 'e.service_id = :service_id';
            $params['service_id'] = (int) $filters['service_id'];
        }

        if (!empty($filters['site_id'])) {
            $where[] = 'e.site_id = :site_id';
            $params['site_id'] = (int) $filters['site_id'];
        }

        if (!empty($filters['brand_id'])) {
            $where[] = 'e.brand_id = :brand_id';
            $params['brand_id'] = (int) $filters['brand_id'];
        }

        // ----- Filtres avancés -----
        if (!empty($filters['date_from'])) {
            $where[] = 'e.acquisition_date >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'e.acquisition_date <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['value_min'])) {
            $where[] = 'e.acquisition_value >= :value_min';
            $params['value_min'] = (float) $filters['value_min'];
        }

        if (!empty($filters['value_max'])) {
            $where[] = 'e.acquisition_value <= :value_max';
            $params['value_max'] = (float) $filters['value_max'];
        }

        if (!empty($filters['under_warranty'])) {
            $where[] = 'e.warranty_end_date IS NOT NULL AND e.warranty_end_date >= CURDATE()';
        }

        if (!empty($filters['without_serial'])) {
            $where[] = '(e.serial_number IS NULL OR e.serial_number = "")';
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Tri par défaut : par numéro d'inventaire croissant (plus logique pour un export)
        $sql = self::BASE_SELECT . $whereSql . ' ORDER BY e.inventory_number ASC';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return array_map(fn($r) => Equipment::fromArray($r), $rows);
    }

    // ============================================
    // CORBEILLE (équipements supprimés)
    // ============================================

    /**
     * Liste paginée des équipements supprimés (corbeille)
     */
    public function findTrashed(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = ['e.deleted_at IS NOT NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(e.inventory_number LIKE :search1
                        OR e.designation LIKE :search2
                        OR e.serial_number LIKE :search3)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params['search1'] = $searchTerm;
            $params['search2'] = $searchTerm;
            $params['search3'] = $searchTerm;
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Compter le total
        $countSql = 'SELECT COUNT(DISTINCT e.id) FROM equipment e' . $whereSql;
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        // Pagination
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        // Récupérer les données
        $sql = self::BASE_SELECT . $whereSql . ' ORDER BY e.deleted_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'data'      => array_map(fn($r) => Equipment::fromArray($r), $rows),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Suppression définitive (hard delete)
     * Ne fonctionne QUE sur les équipements déjà en corbeille (deleted_at non NULL)
     */
    public function forceDelete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM equipment WHERE id = :id AND deleted_at IS NOT NULL');
        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // ÉCRITURE
    // ============================================

    public function create(array $data): int
    {
        // ----- Compléter les champs manquants avec des valeurs par défaut -----
        // (indispensable pour l'import CSV qui n'envoie que certains champs)
        $defaults = [
            'inventory_number'    => null,
            'category_id'         => null,
            'designation'         => null,
            'brand_id'            => null,
            'model_id'            => null,
            'model_text'          => null,
            'serial_number'       => null,
            'site_id'             => null,
            'service_id'          => null,
            'location_id'         => null,
            'responsible_id'      => null,
            'supplier_id'         => null,
            'status_id'           => null,
            'acquisition_date'    => null,
            'commissioning_date'  => null,
            'acquisition_value'   => 0,
            'residual_value'      => 0,
            'amortization_years'  => null,
            'invoice_number'      => null,
            'warranty_end_date'   => null,
            'technical_specs'     => null,
            'notes'               => null,
            'created_by'          => null,
        ];

        $data = array_merge($defaults, $data);

        $sql = 'INSERT INTO equipment (
                    inventory_number, category_id, designation, brand_id, model_id,
                    model_text, serial_number, site_id, service_id, location_id,
                    responsible_id, supplier_id, status_id, acquisition_date, commissioning_date,
                    acquisition_value, residual_value, amortization_years, invoice_number, warranty_end_date,
                    technical_specs, notes, created_by, created_at
                ) VALUES (
                    :inventory_number, :category_id, :designation, :brand_id, :model_id,
                    :model_text, :serial_number, :site_id, :service_id, :location_id,
                    :responsible_id, :supplier_id, :status_id, :acquisition_date, :commissioning_date,
                    :acquisition_value, :residual_value, :amortization_years, :invoice_number, :warranty_end_date,
                    :technical_specs, :notes, :created_by, NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->prepareData($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE equipment SET
                    category_id         = :category_id,
                    designation         = :designation,
                    brand_id            = :brand_id,
                    model_id            = :model_id,
                    model_text          = :model_text,
                    serial_number       = :serial_number,
                    site_id             = :site_id,
                    service_id          = :service_id,
                    location_id         = :location_id,
                    responsible_id      = :responsible_id,
                    supplier_id         = :supplier_id,
                    status_id           = :status_id,
                    acquisition_date    = :acquisition_date,
                    commissioning_date  = :commissioning_date,
                    acquisition_value   = :acquisition_value,
                    residual_value      = :residual_value,
                    amortization_years  = :amortization_years,
                    invoice_number      = :invoice_number,
                    warranty_end_date   = :warranty_end_date,
                    technical_specs     = :technical_specs,
                    notes               = :notes,
                    updated_by          = :updated_by,
                    updated_at          = NOW()
                WHERE id = :id AND deleted_at IS NULL';

        $params = $this->prepareData($data);
        unset($params['inventory_number']); // on ne change pas le numéro d'inventaire
        $params['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function softDelete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipment SET deleted_at = NOW(), updated_by = :user_id WHERE id = :id AND deleted_at IS NULL'
        );
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function restore(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipment SET deleted_at = NULL, updated_at = NOW() WHERE id = :id'
        );
        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // MISE À JOUR RAPIDE DU STATUT
    // ============================================

    /**
     * Change uniquement le statut d'un équipement (utilisé par l'AJAX de la liste).
     */
    public function updateStatus(int $id, int $statusId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE equipment
             SET status_id  = :status_id,
                 updated_by = :updated_by,
                 updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL'
        );

        return $stmt->execute([
            'id'         => $id,
            'status_id'  => $statusId,
            'updated_by' => $userId,
        ]);
    }

    // ============================================
    // ACTIONS GROUPÉES (BULK)
    // ============================================

    /**
     * Met à jour le statut de PLUSIEURS équipements d'un coup.
     *
     * @param int[] $ids
     */
    public function bulkUpdateStatus(array $ids, int $statusId, int $userId): int
    {
        if (empty($ids)) {
            return 0;
        }

        // Sécuriser : ne garder que les entiers positifs
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) {
            return 0;
        }

        // Construire les placeholders nommés :id0, :id1, :id2...
        $placeholders = [];
        $params = ['status_id' => $statusId, 'updated_by' => $userId];
        foreach ($ids as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $sql = 'UPDATE equipment
                SET status_id  = :status_id,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id IN (' . implode(',', $placeholders) . ')
                  AND deleted_at IS NULL';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Met à la corbeille PLUSIEURS équipements d'un coup (soft delete).
     *
     * @param int[] $ids
     */
    public function bulkSoftDelete(array $ids, int $userId): int
    {
        if (empty($ids)) {
            return 0;
        }

        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) {
            return 0;
        }

        $placeholders = [];
        $params = ['user_id' => $userId];
        foreach ($ids as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $sql = 'UPDATE equipment
                SET deleted_at = NOW(),
                    updated_by = :user_id
                WHERE id IN (' . implode(',', $placeholders) . ')
                  AND deleted_at IS NULL';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Récupère PLUSIEURS équipements par leurs IDs.
     *
     * @param int[] $ids
     * @return Equipment[]
     */
    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
        if (empty($ids)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $sql = self::BASE_SELECT
             . ' WHERE e.id IN (' . implode(',', $placeholders) . ')
                 AND e.deleted_at IS NULL
               ORDER BY e.inventory_number ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return array_map(fn($r) => Equipment::fromArray($r), $stmt->fetchAll());
    }

    // ============================================
    // PIÈCES JOINTES (ATTACHMENTS)  ← NOUVEAU
    // ============================================

    /**
     * Récupère toutes les pièces jointes d'un équipement.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAttachments(int $equipmentId): array
{
    $stmt = $this->db->prepare(
        'SELECT a.*,
                CONCAT(u.first_name, \' \', u.last_name) AS uploaded_by_name
         FROM equipment_attachments a
         LEFT JOIN users u ON u.id = a.uploaded_by
         WHERE a.equipment_id = :equipment_id
         ORDER BY a.created_at DESC'
    );
    $stmt->execute(['equipment_id' => $equipmentId]);

    return $stmt->fetchAll();
}

    /**
     * Récupère une pièce jointe par son ID.
     *
     * @return array<string, mixed>|null
     */
    public function findAttachment(int $attachmentId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM equipment_attachments WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $attachmentId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Enregistre une nouvelle pièce jointe en base.
     */
    public function createAttachment(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO equipment_attachments
                (equipment_id, original_name, stored_name, mime_type, size_bytes, category, uploaded_by, created_at)
             VALUES
                (:equipment_id, :original_name, :stored_name, :mime_type, :size_bytes, :category, :uploaded_by, NOW())'
        );

        $stmt->execute([
            'equipment_id'  => (int) $data['equipment_id'],
            'original_name' => $data['original_name'],
            'stored_name'   => $data['stored_name'],
            'mime_type'     => $data['mime_type'],
            'size_bytes'    => (int) $data['size_bytes'],
            'category'      => $data['category'] ?? null,
            'uploaded_by'   => $data['uploaded_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Supprime une pièce jointe en base.
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM equipment_attachments WHERE id = :id');
        return $stmt->execute(['id' => $attachmentId]);
    }

    /**
     * Compte les pièces jointes d'un équipement.
     */
    public function countAttachments(int $equipmentId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM equipment_attachments WHERE equipment_id = :equipment_id'
        );
        $stmt->execute(['equipment_id' => $equipmentId]);

        return (int) $stmt->fetchColumn();
    }

    // ============================================
    // IMPORT EN MASSE (CSV)
    // ============================================

    /**
     * Insère plusieurs équipements d'un coup (bulk insert).
     * Chaque entrée doit contenir les clés attendues par create().
     *
     * @param array<int, array<string, mixed>> $rows
     * @return int Nombre d'équipements insérés
     */
    public function bulkInsert(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $inserted = 0;
        $this->db->beginTransaction();

        try {
            foreach ($rows as $row) {
                $this->create($row);
                $inserted++;
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $inserted;
    }

    /**
     * Récupère toutes les catégories indexées par code.
     *
     * @return array<string, int> ['pc' => 1, 'imp' => 2, ...]
     */
    public function getCategoriesByCode(): array
    {
        $stmt = $this->db->query('SELECT id, code FROM equipment_categories');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtolower((string) $row['code'])] = (int) $row['id'];
        }
        return $result;
    }

    /**
     * Récupère tous les statuts indexés par code.
     *
     * @return array<string, int>
     */
    public function getStatusesByCode(): array
    {
        $stmt = $this->db->query('SELECT id, code FROM equipment_statuses');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtolower((string) $row['code'])] = (int) $row['id'];
        }
        return $result;
    }

    /**
     * Récupère toutes les marques indexées par nom (en minuscules).
     *
     * @return array<string, int>
     */
    public function getBrandsByName(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM brands');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtolower(trim((string) $row['name']))] = (int) $row['id'];
        }
        return $result;
    }

    /**
     * Récupère tous les services indexés par nom.
     *
     * @return array<string, int>
     */
    public function getServicesByName(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM services WHERE is_active = 1');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtolower(trim((string) $row['name']))] = (int) $row['id'];
        }
        return $result;
    }

    /**
     * Récupère tous les sites indexés par nom.
     *
     * @return array<string, int>
     */
    public function getSitesByName(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM sites WHERE is_active = 1');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtolower(trim((string) $row['name']))] = (int) $row['id'];
        }
        return $result;
    }

    /**
     * Récupère tous les numéros d'inventaire existants (pour détecter les doublons).
     *
     * @return array<string, bool> ['INF-2026-0001' => true, ...]
     */
    public function getExistingInventoryNumbers(): array
    {
        $stmt = $this->db->query('SELECT inventory_number FROM equipment');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtoupper((string) $row['inventory_number'])] = true;
        }
        return $result;
    }

    /**
     * Récupère tous les numéros de série existants (pour détecter les doublons).
     *
     * @return array<string, bool>
     */
    public function getExistingSerialNumbers(): array
    {
        $stmt = $this->db->query('SELECT serial_number FROM equipment WHERE serial_number IS NOT NULL');
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[strtoupper((string) $row['serial_number'])] = true;
        }
        return $result;
    }

    // ============================================
    // GÉNÉRATION DE CODE
    // ============================================

    public function generateNextInventoryNumber(): string
    {
        $prefix = 'INF';
        $year = date('Y');

        // Récupérer le dernier numéro de l'année en cours
        $stmt = $this->db->prepare(
            'SELECT inventory_number FROM equipment
             WHERE inventory_number LIKE :pattern
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['pattern' => "$prefix-$year-%"]);
        $last = $stmt->fetchColumn();

        if ($last) {
            // Extraire la partie numérique : INF-2026-0042 → 42
            $parts = explode('-', $last);
            $number = (int) end($parts);
            $next = $number + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $next);
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countByStatus(): array
    {
        $sql = 'SELECT s.code, COUNT(e.id) AS total
                FROM equipment_statuses s
                LEFT JOIN equipment e ON e.status_id = s.id AND e.deleted_at IS NULL
                GROUP BY s.id, s.code
                ORDER BY s.sort_order';

        $stmt = $this->db->query($sql);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['code']] = (int) $row['total'];
        }
        return $result;
    }

    /**
     * Compte les équipements par statut avec tous les détails
     * (id, nom, code, couleur, ordre d'affichage + count).
     *
     * Utilisé pour la barre de badges cliquables en haut de la liste.
     *
     * @return array<int, array{id: int, name: string, code: string, color: string, count: int}>
     */
    public function countByStatusDetailed(): array
    {
        $sql = 'SELECT s.id,
                       s.name,
                       s.code,
                       s.color,
                       s.sort_order,
                       COUNT(e.id) AS total
                FROM equipment_statuses s
                LEFT JOIN equipment e
                       ON e.status_id = s.id
                      AND e.deleted_at IS NULL
                GROUP BY s.id, s.name, s.code, s.color, s.sort_order
                ORDER BY s.sort_order';

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll();

        return array_map(fn($r) => [
            'id'    => (int) $r['id'],
            'name'  => (string) $r['name'],
            'code'  => (string) $r['code'],
            'color' => (string) ($r['color'] ?? '#6c757d'),
            'count' => (int) $r['total'],
        ], $rows);
    }

    public function countByCategory(): array
    {
        $sql = 'SELECT c.id, c.name, COUNT(e.id) AS total
                FROM equipment_categories c
                LEFT JOIN equipment e ON e.category_id = c.id AND e.deleted_at IS NULL
                GROUP BY c.id, c.name
                ORDER BY total DESC';

        $stmt = $this->db->query($sql);
        return array_map(fn($r) => [
            'id'    => (int) $r['id'],
            'name'  => $r['name'],
            'count' => (int) $r['total'],
        ], $stmt->fetchAll());
    }

    public function getTotalValue(): float
    {
        $stmt = $this->db->query(
            'SELECT COALESCE(SUM(acquisition_value), 0) FROM equipment WHERE deleted_at IS NULL'
        );
        return (float) $stmt->fetchColumn();
    }

    public function getRecent(int $limit = 5): array
    {
        $sql = self::BASE_SELECT . ' WHERE e.deleted_at IS NULL ORDER BY e.created_at DESC LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Equipment::fromArray($r), $stmt->fetchAll());
    }

    // ============================================
    // ALERTES GARANTIE
    // ============================================

    /**
     * Récupère les équipements dont la garantie expire dans les N prochains jours.
     *
     * @param int $days Nombre de jours à venir (par défaut 30)
     * @return Equipment[]
     */
    public function findExpiringWarranties(int $days = 30): array
    {
        $sql = self::BASE_SELECT . '
            WHERE e.deleted_at IS NULL
              AND e.warranty_end_date IS NOT NULL
              AND e.warranty_end_date >= CURDATE()
              AND e.warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY e.warranty_end_date ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Equipment::fromArray($r), $stmt->fetchAll());
    }

    /**
     * Récupère les équipements dont la garantie a expiré récemment.
     *
     * @param int $days Nombre de jours dans le passé (par défaut 30)
     * @return Equipment[]
     */
    public function findRecentlyExpiredWarranties(int $days = 30): array
    {
        $sql = self::BASE_SELECT . '
            WHERE e.deleted_at IS NULL
              AND e.warranty_end_date IS NOT NULL
              AND e.warranty_end_date < CURDATE()
              AND e.warranty_end_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            ORDER BY e.warranty_end_date DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Equipment::fromArray($r), $stmt->fetchAll());
    }

    /**
     * Compte les équipements dont la garantie expire bientôt.
     */
    public function countExpiringWarranties(int $days = 30): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM equipment
             WHERE deleted_at IS NULL
               AND warranty_end_date IS NOT NULL
               AND warranty_end_date >= CURDATE()
               AND warranty_end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)'
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Compte les équipements dont la garantie a expiré récemment.
     */
    public function countRecentlyExpiredWarranties(int $days = 30): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM equipment
             WHERE deleted_at IS NULL
               AND warranty_end_date IS NOT NULL
               AND warranty_end_date < CURDATE()
               AND warranty_end_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)'
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================

    /**
     * Prépare les données pour l'insertion/mise à jour
     * (convertit les valeurs vides en NULL)
     */
    private function prepareData(array $data): array
    {
        $fields = [
            'inventory_number', 'category_id', 'designation', 'brand_id', 'model_id',
            'model_text', 'serial_number', 'site_id', 'service_id', 'location_id',
            'responsible_id', 'supplier_id', 'status_id', 'acquisition_date', 'commissioning_date',
            'acquisition_value', 'residual_value', 'amortization_years', 'invoice_number', 'warranty_end_date',
            'technical_specs', 'notes', 'created_by', 'updated_by',
        ];

        $result = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field];
            // Convertir les chaînes vides en NULL
            if ($value === '' || $value === null) {
                $result[$field] = null;
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}