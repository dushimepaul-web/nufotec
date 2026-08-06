<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Media_models extends CI_Model {

    protected $table = 'galerie_medias';

    public function __construct()
    {
        parent::__construct();
    }

    public function create($data, $return_id = false)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $query = $this->db->insert($this->table, $data);
        if ($query) {
            return $return_id ? $this->db->insert_id() : true;
        }
        log_message('error', "Media_models create failed: " . $this->db->error()['message']);
        return false;
    }

    public function read($where = [], $order_by = 'id_media', $order = 'DESC', $limit = null, $offset = 0)
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        $this->db->order_by($order_by, $order);
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get($this->table)->result_array();
    }

    public function readOne($where)
    {
        return $this->db->get_where($this->table, $where)->row_array();
    }

    public function update($where, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where($where);
        $query = $this->db->update($this->table, $data);
        if (!$query) {
            log_message('error', "Media_models update failed: " . $this->db->error()['message']);
        }
        return (bool) $query;
    }

    public function delete($where)
    {
        $this->db->where($where);
        $query = $this->db->delete($this->table);
        if (!$query) {
            log_message('error', "Media_models delete failed: " . $this->db->error()['message']);
        }
        return (bool) $query;
    }

    public function count($where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($this->table);
    }

    public function getByType($type, $limit = null, $offset = 0)
    {
        return $this->read(['type' => $type], 'id_media', 'DESC', $limit, $offset);
    }

    public function getStatistics()
    {
        $types = ['audio', 'video', 'image', 'document', 'link'];
        $stats = [];
        foreach ($types as $type) {
            $stats[$type] = 0;
            $stats[$type . '_size'] = 0;
        }
        $stats['audio_duration'] = 0;
        $stats['video_duration'] = 0;

        // Q2: une seule requête GROUP BY au lieu de N requêtes séparées
        $query = $this->db
            ->select("type, COUNT(*) AS cnt, COALESCE(SUM(taille), 0) AS total_size, COALESCE(SUM(CASE WHEN type IN ('audio','video') THEN duree ELSE 0 END), 0) AS total_duration")
            ->group_by('type')
            ->get($this->table)
            ->result_array();

        foreach ($query as $row) {
            $t = $row['type'];
            $stats[$t] = (int)$row['cnt'];
            $stats[$t . '_size'] = (int)$row['total_size'];
            if ($t === 'audio') $stats['audio_duration'] = (int)$row['total_duration'];
            if ($t === 'video') $stats['video_duration'] = (int)$row['total_duration'];
        }

        $stats['total'] = array_sum(array_intersect_key($stats, array_flip($types)));
        return $stats;
    }

    public function getCategories($type = null)
    {
        $this->db->distinct();
        $this->db->select('categorie');
        if ($type) {
            $this->db->where('type', $type);
        }
        $this->db->where('categorie IS NOT NULL');
        $this->db->where('categorie !=', '');
        $this->db->order_by('categorie', 'ASC');
        $result = $this->db->get($this->table)->result_array();
        return array_column($result, 'categorie');
    }

    public function search($query, $type = null, $limit = 50)
    {
        $this->db->from($this->table);
        $terms = $this->db->escape_str($query);
        $this->db->where("MATCH(titre, description, credits) AGAINST('$terms*' IN BOOLEAN MODE)", null, false);
        if ($type) {
            $this->db->where('type', $type);
        }
        $this->db->order_by('id_media', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result_array();
    }

    public function toggleField($id, $field)
    {
        $media = $this->readOne(['id_media' => $id]);
        if (!$media) return false;
        $new_value = empty($media[$field]) ? 1 : 0;
        return $this->update(['id_media' => $id], [$field => $new_value]);
    }

    // ==================== FILE DE JOBS (traitement asynchrone) ====================

    private function jobDir()
    {
        $dir = FCPATH . 'uploads/temp/jobs/';
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0777, true)) {
                return $dir;
            }
            @chmod($dir, 0777);
        }
        // Auto-réparation des permissions (ex: dossier créé par un autre utilisateur)
        if (!is_writable($dir)) {
            @chmod($dir, 0777);
        }
        return $dir;
    }

    public function enqueueJob($type, $payload)
    {
        $id = 'job_' . date('YmdHis') . '_' . bin2hex(random_bytes(6));
        $data = [
            'id'         => $id,
            'type'       => $type,
            'payload'    => $payload,
            'status'     => 'pending',
            'attempts'   => 0,
            'error'      => null,
            'created_at' => date('Y-m-d H:i:s'),
            'started_at' => null,
            'finished_at'=> null,
            'result'     => null,
        ];
        $written = @file_put_contents($this->jobDir() . $id . '.json', json_encode($data), LOCK_EX);
        return ($written === false) ? null : $id;
    }

    public function getNextJob()
    {
        $dir = $this->jobDir();
        $files = glob($dir . '*.json');
        if (!$files) return null;

        $stale_threshold = strtotime('-15 minutes');
        $candidates = [];
        foreach ($files as $file) {
            $data = json_decode(@file_get_contents($file), true);
            if (!is_array($data)) continue;
            $status = $data['status'] ?? 'pending';
            if ($status === 'pending') {
                $data['_file'] = $file;
                $candidates[] = $data;
            } elseif ($status === 'processing' && !empty($data['started_at']) && strtotime($data['started_at']) < $stale_threshold) {
                $data['_file'] = $file;
                $candidates[] = $data;
            } elseif ($status === 'failed' && (int)($data['attempts'] ?? 0) < 3) {
                $data['_file'] = $file;
                $candidates[] = $data;
            }
        }
        if (!$candidates) return null;

        usort($candidates, function ($a, $b) {
            return strtotime($a['created_at'] ?? 0) <=> strtotime($b['created_at'] ?? 0);
        });
        return $candidates[0];
    }

    public function claimJob($id)
    {
        $file = $this->jobDir() . $id . '.json';
        if (!is_file($file)) return null;

        $fp = @fopen($file, 'c+');
        if (!$fp) return null;
        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return null;
        }

        $raw = stream_get_contents($fp);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return null;
        }

        if (($data['status'] ?? '') === 'processing') {
            flock($fp, LOCK_UN);
            fclose($fp);
            return null;
        }

        $data['status']     = 'processing';
        $data['started_at'] = date('Y-m-d H:i:s');

        ftruncate($fp, 0);
        rewind($fp);
        $written = fwrite($fp, json_encode($data));
        flock($fp, LOCK_UN);
        fclose($fp);

        return ($written === false) ? null : $data;
    }

    public function finishJob($id, $status, $result = null, $error = null)
    {
        $file = $this->jobDir() . $id . '.json';
        if (!is_file($file)) return false;
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) return false;
        $data['status'] = $status;
        $data['finished_at'] = date('Y-m-d H:i:s');
        if ($status === 'failed') {
            $data['attempts'] = (int)($data['attempts'] ?? 0) + 1;
            $data['error'] = $error;
        }
        if ($result !== null) $data['result'] = $result;
        return @file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }

    public function jobDoneFor($relative_path)
    {
        $dir = $this->jobDir();
        $files = glob($dir . '*.json');
        if (!$files) return null;
        foreach ($files as $file) {
            $data = json_decode(@file_get_contents($file), true);
            if (!is_array($data) || ($data['status'] ?? '') !== 'done') continue;
            if (($data['payload']['relative_path'] ?? null) !== $relative_path) continue;
            $data['_file'] = $file;
            return $data;
        }
        return null;
    }

    // ==================== DOUBLONS (NOM DE FICHIER / TITRE) ====================

    public function checkDuplicate($file_name, $titre = null, $type = null)
    {
        $file_name = basename($file_name);
        $ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $base = strtolower(pathinfo($file_name, PATHINFO_FILENAME));

        // 1) même nom de fichier (les fichiers stockés portent un préfixe horodaté YmdHis_)
        $this->db->from($this->table);
        $this->db->like('fichier', $base . '.' . $ext);
        if ($type) {
            $this->db->where('type', $type);
        }
        $rows = $this->db->get()->result_array();
        foreach ($rows as $r) {
            $incoming_base = preg_replace('/^\d{14}_/', '', $base);
            $stored_base   = preg_replace('/^\d{14}_/', '', strtolower(pathinfo($r['fichier'], PATHINFO_FILENAME)));
            if ($stored_base === $incoming_base && strtolower(pathinfo($r['fichier'], PATHINFO_EXTENSION)) === $ext) {
                return ['kind' => 'file', 'media' => $r];
            }
        }

        // 2) même titre
        if ($titre !== null && trim($titre) !== '') {
            $this->db->from($this->table);
            $this->db->where('titre', trim($titre));
            $this->db->where('type', $type);
            $r = $this->db->get()->row_array();
            if ($r) {
                return ['kind' => 'titre', 'media' => $r];
            }
        }

        return null;
    }

    // ==================== RECHERCHE PAR FICHIER ====================

    public function searchByFile($term, $type = null, $limit = null, $offset = 0)
    {
        $this->db->from($this->table);
        $this->db->like('fichier', $term, 'both');
        if ($type) {
            $this->db->where('type', $type);
        }
        $this->db->order_by('id_media', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }
        return $this->db->get()->result_array();
    }

    public function countSearchByFile($term, $type = null)
    {
        $this->db->from($this->table);
        $this->db->like('fichier', $term, 'both');
        if ($type) {
            $this->db->where('type', $type);
        }
        return $this->db->count_all_results();
    }
}