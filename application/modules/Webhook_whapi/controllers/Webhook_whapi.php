<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webhook_whapi extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('whapi_library');
        $this->load->helper('whapi');
    }

    public function index($token = null) {
        $raw_input = file_get_contents('php://input');
        $input     = json_decode($raw_input, true);
        while (ob_get_level() > 0) { ob_end_clean(); }
        http_response_code(200);
        header('Content-Type: application/json');
        header('Connection: close');
        header('Content-Encoding: none');
        $rb = json_encode(['status' => 'received']);
        header('Content-Length: ' . strlen($rb));
        echo $rb;
        ob_start(); ob_end_flush(); flush();
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
        if (!$input) return;
        $expected_token = $this->whapi_library->get_setting('webhook_token');
        if (!empty($expected_token)) {
            $url_token = $token ?? $this->input->get('token');
            if ($url_token !== $expected_token) return;
        }
        $this->process_message($input);
    }

    private function process_message($payload) {
        if (!empty($payload['fromMe']) || !empty($payload['isFromMe'])) return;
        $message_type = $payload['type'] ?? 'unknown';
        $message_id   = $payload['id'] ?? null;
        $sender = null;
        if (isset($payload['from']['phone']))      $sender = $payload['from']['phone'];
        elseif (isset($payload['from']))           $sender = $payload['from'];
        elseif (isset($payload['author']))         $sender = $payload['author'];
        $chat_id = null; $is_group = false;
        if (isset($payload['chat']['id']))         { $chat_id = $payload['chat']['id']; $is_group = strpos($chat_id,'@g.us')!==false; }
        elseif (isset($payload['chatId']))         { $chat_id = $payload['chatId'];     $is_group = strpos($chat_id,'@g.us')!==false; }
        $message_text = '';
        if ($message_type==='text' && isset($payload['text']['body']))      $message_text = $payload['text']['body'];
        elseif ($message_type==='text' && isset($payload['text']))          $message_text = is_string($payload['text']) ? $payload['text'] : ($payload['text']['body']??'');
        elseif (isset($payload['text']))                                     $message_text = is_string($payload['text']) ? $payload['text'] : '';
        $media_types=['image','video','audio','document','sticker'];
        $has_media = in_array($message_type,$media_types);
        $media_url = null;
        if ($has_media && isset($payload[$message_type])) {
            $media_url = $payload[$message_type]['url'] ?? $payload[$message_type]['link'] ?? null;
            $cap = $payload[$message_type]['caption'] ?? null;
            if ($cap) $message_text = $cap;
        }
        if (!$sender) return;
        $message_text = sanitize_message($message_text);
        $sender_clean = format_phone($sender);
        $bl = $this->db->where('phone_number',$sender_clean)->get('whatsapp_blacklist')->num_rows()>0;
        if ($bl) return;
        $admin_numbers = json_decode($this->whapi_library->get_setting('admin_numbers')?:'[]',true)?:[];
        $is_admin = in_array($sender_clean,$admin_numbers);
        if (!$is_admin && $is_group && $chat_id)
            $is_admin = $this->db->where('groupe_id',$chat_id)->where('phone_formatted',$sender_clean)->where('is_admin',1)->get('whatsapp_participants')->num_rows()>0;
        if ($is_group && $chat_id) {
            $this->_upsert_group($chat_id,$payload['chat']['name']??$payload['chatName']??null);
            $this->_upsert_participant($chat_id,$sender,$payload['pushName']??$payload['notifyName']??null);
        }
        $master_group_id = $this->whapi_library->get_setting('master_group_id');
        $is_master_group = ($chat_id===$master_group_id);
        if (!$is_admin) {
            $viol=false; $vreason='';
            if ($has_media)                          {$viol=true;$vreason='media_non_autorise';}
            elseif (contains_link($message_text))    {$viol=true;$vreason='lien_non_autorise';}
            elseif (contains_mention($message_text)) {$viol=true;$vreason='mention_non_autorisee';}
            elseif (contains_phone($message_text))   {$viol=true;$vreason='phone_number_non_autorise';}
            if ($viol) {
                if ($message_id) $this->whapi_library->delete_message($message_id);
                $this->db->insert('whatsapp_security_logs',['group_id'=>$chat_id,'sender'=>$sender_clean,'action_type'=>$vreason,'reason'=>'auto-delete non-admin','created_at'=>date('Y-m-d H:i:s')]);
                $this->_increment_violation($sender);
            }
            return;
        }
        if (!$is_master_group) return;
        $target_type='both';
        if (strpos($message_text,'#groupe')===0)      {$target_type='group';  $message_text=trim(substr($message_text,7));}
        elseif (strpos($message_text,'#inbox')===0)   {$target_type='inbox';  $message_text=trim(substr($message_text,6));}
        elseif (strpos($message_text,'#template:')===0){
            preg_match('/#template:([a-zA-Z0-9_]+)/',$message_text,$m);
            if (!empty($m[1])){$t=$this->db->get_where('whatsapp_templates',['name'=>$m[1]])->row();if($t)$message_text=$t->content;}
            $message_text=preg_replace('/#template:[a-zA-Z0-9_]+\s*/','', $message_text);
        }
        $message_text=sanitize_message($message_text);
        log_whatsapp(null,null,$sender,$message_text,$message_type,'received');
        $result=$this->whapi_library->distribute_message(['type'=>$message_type,'text'=>$message_text,'group_id'=>$chat_id,'sender'=>$sender,'message_id'=>$message_id,'target_type'=>$target_type,'has_media'=>$has_media,'media_url'=>$media_url,'media_type'=>$message_type],$sender);
        log_message('info',"Broadcast $sender -> $target_type: ".json_encode($result));
    }

    private function _upsert_group($gid,$nom=null){
        $e=$this->db->get_where('groupes_whatsapp',['groupe_id'=>$gid])->row();
        $d=['groupe_id'=>$gid,'updated_at'=>date('Y-m-d H:i:s')];if($nom)$d['nom']=$nom;
        if($e)$this->db->where('id',$e->id)->update('groupes_whatsapp',$d);
        else{$d['actif']=1;$d['created_at']=date('Y-m-d H:i:s');$this->db->insert('groupes_whatsapp',$d);}
    }

    private function _upsert_participant($gid,$phone,$name=null){
        $pf=format_phone($phone);
        $e=$this->db->where('groupe_id',$gid)->where('phone_formatted',$pf)->get('whatsapp_participants')->row();
        $d=['groupe_id'=>$gid,'phone'=>$phone,'phone_formatted'=>$pf,'synced_at'=>date('Y-m-d H:i:s')];
        if($name)$d['profile_name']=$name;
        if($e)$this->db->where('id',$e->id)->update('whatsapp_participants',$d);
        else{$d['is_admin']=0;$d['created_at']=date('Y-m-d H:i:s');$this->db->insert('whatsapp_participants',$d);}
    }

    private function _increment_violation($phone){
        $pf=format_phone($phone);
        $p=$this->db->where('phone_formatted',$pf)->get('whatsapp_participants')->row();
        if(!$p)return;
        $n=($p->violation_count??0)+1;
        $this->db->where('phone_formatted',$pf)->update('whatsapp_participants',['violation_count'=>$n]);
        if($n>=5){
            $a=$this->db->get_where('whatsapp_blacklist',['phone_number'=>$pf])->row();
            if(!$a)$this->db->insert('whatsapp_blacklist',['phone_number'=>$pf,'reason'=>'Auto-blacklist: 5 violations','created_at'=>date('Y-m-d H:i:s')]);
        }
    }
}