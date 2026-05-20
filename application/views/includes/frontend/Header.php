<?php
// ================================================================
// HEADER NUFOTEC BURUNDI — Responsive + 50 langues + Traduction fiable
// ================================================================
defined('BASEPATH') OR exit('No direct script access allowed');

$is_product_page = (isset($product) && !empty($product) && isset($product['title']));
if ($is_product_page) {
    $page_title = htmlspecialchars($product['title']) . ' - ' . htmlspecialchars($this->Model->get_setting('site_name','NUFOTEC BURUNDI'));
    $page_desc  = !empty($product['description']) ? substr(htmlspecialchars($product['description']),0,160) : 'Produit NUFOTEC';
    $page_image = base_url('attachments/Products/'.$product['main_image']);
    $page_url   = base_url('Products/detail/'.($product['slug'] ?? $product['id']));
} else {
    $page_title = htmlspecialchars($this->Model->get_setting('site_name','NUFOTEC BURUNDI'));
    $page_desc  = htmlspecialchars($this->Model->get_setting('agf_description_courte','Projet integre de transformation agro-alimentaire'));
    $site_logo  = $this->Model->get_setting('site_logo','assets/fro.png');
    $page_image = base_url('attachments/Configurations/'.$site_logo);
    $page_url   = base_url();
}
$logged_in  = $this->session->userdata('logged_in') === TRUE;
$user_name  = $this->session->userdata('username');
$user_photo = $this->session->userdata('photo');
$initials   = '?';
if ($logged_in && !empty($user_name)) {
    $parts    = explode(' ', trim($user_name));
    $initials = count($parts) >= 2
        ? strtoupper(substr($parts[0],0,1).substr($parts[1],0,1))
        : strtoupper(substr($user_name,0,2));
}
?>
<!DOCTYPE html>
<html lang="fr" id="htmlRoot">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?></title>
<meta name="description" content="<?= $page_desc ?>">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#0f4c3a">
<meta property="og:type"        content="<?= $is_product_page ? 'product' : 'website' ?>">
<meta property="og:url"         content="<?= $page_url ?>">
<meta property="og:title"       content="<?= $page_title ?>">
<meta property="og:description" content="<?= $page_desc ?>">
<meta property="og:image"       content="<?= $page_image ?>">
<meta name="twitter:card"       content="summary_large_image">
<meta name="twitter:title"      content="<?= $page_title ?>">
<meta name="twitter:image"      content="<?= $page_image ?>">
<link rel="icon" href="<?= base_url('attachments/Configurations/'.$this->Model->get_setting('favicon_ico','assets/fro.png')) ?>" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/backend/css/bootstrap.min.css') ?>" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">

<style>
/* ================================================================
   RESET & VARIABLES
================================================================ */
:root {
  --primary:        #0f4c3a;
  --primary-dk:     #0a3326;
  --primary-lt:     #1a5f4a;
  --primary-ltr:    #e8f5f0;
  --accent:         #d4af37;
  --accent-hv:      #b8941f;
  --light:          #f8faf9;
  --dark:           #1a1a1a;
  --gray:           #64748b;
  --gray-lt:        #e2e8f0;
  --white:          #ffffff;
  --danger:         #dc3545;
  --h-top:          36px;
  --h-header:       68px;
  --h-nav:          52px;
  --h-bottom:       62px;
  --r-sm:           8px;
  --r-md:           12px;
  --r-lg:           16px;
  --r-xl:           20px;
  --sh-sm:          0 1px 3px rgba(0,0,0,.08);
  --sh-md:          0 4px 12px rgba(0,0,0,.1);
  --sh-lg:          0 10px 30px rgba(0,0,0,.12);
  --sh-xl:          0 20px 40px rgba(0,0,0,.14);
  --tr:             0.25s cubic-bezier(.4,0,.2,1);
  --tr-slow:        0.4s cubic-bezier(.165,.84,.44,1);
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; -webkit-tap-highlight-color:transparent; }
html { scroll-behavior:smooth; }
body {
  font-family:'Inter',sans-serif;
  background:var(--light);
  color:var(--dark);
  padding-top:calc(var(--h-top) + var(--h-header) + var(--h-nav));
  overflow-x:hidden;
  line-height:1.6;
}

/* ================================================================
   GOOGLE TRANSLATE — MASQUER COMPLETEMENT
================================================================ */
.goog-te-banner-frame, iframe.skiptranslate,
body > .skiptranslate { display:none!important; height:0!important; }
body { top:0!important; margin-top:0!important; }
.goog-te-gadget { font-size:0!important; color:transparent!important; }
#google_translate_element { display:none!important; }

/* ================================================================
   TOP BAR
================================================================ */
.nuf-topbar {
  position:fixed; top:0; left:0; right:0; z-index:1040;
  height:var(--h-top);
  background:var(--primary-dk);
  color:rgba(255,255,255,.9);
  font-size:12px;
  border-bottom:1px solid rgba(212,175,55,.2);
  transition:transform var(--tr);
  display:flex; align-items:center;
}
.nuf-topbar.hidden { transform:translateY(-100%); }
.nuf-topbar-inner {
  max-width:1400px; margin:0 auto;
  width:100%; padding:0 20px;
  display:flex; justify-content:space-between; align-items:center; gap:12px;
}
.nuf-topbar-side { display:flex; align-items:center; gap:16px; }
.nuf-topbar a {
  color:rgba(255,255,255,.9); text-decoration:none;
  display:flex; align-items:center; gap:5px;
  font-weight:500; white-space:nowrap; font-size:12px;
  transition:color var(--tr);
}
.nuf-topbar a:hover { color:var(--accent); }
.nuf-topbar i { color:var(--accent); font-size:12px; }
.nuf-topbar-div { width:1px; height:14px; background:rgba(212,175,55,.3); }

/* ================================================================
   MAIN HEADER
================================================================ */
.nuf-header {
  position:fixed; top:var(--h-top); left:0; right:0; z-index:1030;
  height:var(--h-header);
  background:rgba(255,255,255,.98);
  backdrop-filter:blur(20px);
  box-shadow:var(--sh-sm);
  transition:top var(--tr), transform var(--tr-slow), box-shadow var(--tr);
}
.nuf-header.scrolled { top:0; box-shadow:var(--sh-md); }
.nuf-header.hide-up  { transform:translateY(-100%); }
.nuf-header-inner {
  max-width:1400px; margin:0 auto;
  height:100%; padding:0 20px;
  display:flex; align-items:center; gap:14px;
}

/* Brand */
.nuf-brand {
  display:flex; align-items:center; gap:10px;
  text-decoration:none; flex-shrink:0;
}
.nuf-brand-logo {
  width:44px; height:44px; border-radius:var(--r-sm);
  background:linear-gradient(135deg,var(--primary),var(--primary-lt));
  display:flex; align-items:center; justify-content:center;
  overflow:hidden; flex-shrink:0;
  box-shadow:0 3px 10px rgba(15,76,58,.25);
  transition:transform var(--tr), box-shadow var(--tr);
}
.nuf-brand:hover .nuf-brand-logo { transform:scale(1.05); box-shadow:0 5px 16px rgba(15,76,58,.35); }
.nuf-brand-logo img { width:100%; height:100%; object-fit:cover; }
.nuf-brand-text h1 {
  font-family:'Playfair Display',serif;
  font-size:19px; font-weight:700;
  color:var(--primary); margin:0; line-height:1.2; white-space:nowrap;
}
.nuf-brand-text span {
  display:block; font-size:9px; font-weight:700;
  color:var(--accent); text-transform:uppercase; letter-spacing:1.5px;
}

/* Search */
.nuf-search {
  flex:1; max-width:460px; position:relative; transition:var(--tr);
}
.nuf-search-input {
  width:100%; height:40px;
  padding:0 42px 0 16px;
  border:2px solid var(--gray-lt); border-radius:20px;
  font-size:14px; font-family:'Inter',sans-serif;
  background:var(--light); color:var(--dark);
  transition:border-color var(--tr), box-shadow var(--tr), background var(--tr);
  outline:none;
}
.nuf-search-input:focus {
  border-color:var(--primary); background:var(--white);
  box-shadow:0 0 0 3px rgba(15,76,58,.1);
}
.nuf-search-btn {
  position:absolute; right:4px; top:50%; transform:translateY(-50%);
  width:32px; height:32px; border-radius:50%;
  background:var(--primary); border:none; color:white;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:13px; transition:var(--tr);
}
.nuf-search-btn:hover { background:var(--accent); }
.nuf-search-results {
  display:none; position:absolute; top:calc(100% + 8px);
  left:0; right:0; background:white; border-radius:var(--r-md);
  box-shadow:var(--sh-xl); max-height:380px; overflow-y:auto;
  border:1px solid var(--gray-lt); z-index:100;
}
.nuf-search-results.open { display:block; }
.nuf-search-cat { padding:7px 14px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--primary); background:var(--primary-ltr); }
.nuf-search-item { display:flex; align-items:center; gap:10px; padding:11px 14px; text-decoration:none; color:var(--dark); border-bottom:1px solid var(--gray-lt); transition:var(--tr); font-size:13px; }
.nuf-search-item:hover { background:var(--light); padding-left:18px; }
.nuf-search-item i { color:var(--primary); font-size:15px; width:18px; }

/* Actions */
.nuf-actions { display:flex; align-items:center; gap:6px; flex-shrink:0; }
.nuf-action-btn {
  display:flex; align-items:center; justify-content:center; gap:5px;
  padding:9px; border-radius:var(--r-sm); border:none; background:transparent;
  color:var(--dark); font-size:13px; font-weight:600; cursor:pointer;
  text-decoration:none; position:relative; transition:var(--tr);
  flex-shrink:0;
}
.nuf-action-btn:hover { background:var(--primary-ltr); color:var(--primary); }
.nuf-action-btn i { font-size:21px; color:var(--primary); transition:color var(--tr); }
.nuf-action-btn:hover i { color:var(--accent); }
.nuf-action-btn .nuf-badge {
  position:absolute; top:3px; right:3px;
  background:var(--danger); color:white;
  font-size:9px; font-weight:700;
  width:17px; height:17px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  border:2px solid white;
}
.nuf-avatar {
  width:34px; height:34px; border-radius:50%;
  border:2px solid var(--gray-lt); object-fit:cover;
  transition:border-color var(--tr), transform var(--tr);
}
.nuf-action-btn:hover .nuf-avatar { border-color:var(--accent); transform:scale(1.05); }
.nuf-initials {
  width:34px; height:34px; border-radius:50%;
  background:linear-gradient(135deg,var(--primary),var(--primary-lt));
  color:white; font-size:12px; font-weight:700;
  display:flex; align-items:center; justify-content:center;
  border:2px solid var(--gray-lt);
}

/* Hamburger */
.nuf-hamburger {
  display:none;
  width:40px; height:40px; border-radius:var(--r-sm);
  border:none; background:var(--primary-ltr); color:var(--primary);
  font-size:24px; cursor:pointer; flex-shrink:0;
  align-items:center; justify-content:center;
  transition:var(--tr);
}
.nuf-hamburger:hover, .nuf-hamburger.open { background:var(--primary); color:white; }

/* ================================================================
   LANGUAGE SELECTOR — DESKTOP
================================================================ */
.nuf-lang {
  position:relative; flex-shrink:0;
}
.nuf-lang-btn {
  display:flex; align-items:center; gap:7px;
  padding:8px 12px; border-radius:var(--r-sm);
  border:1.5px solid var(--gray-lt); background:white;
  cursor:pointer; font-family:'Inter',sans-serif;
  font-size:13px; font-weight:500; color:var(--dark);
  transition:var(--tr); white-space:nowrap;
}
.nuf-lang-btn:hover { border-color:var(--accent); background:var(--primary-ltr); }
.nuf-lang-btn img { width:20px; height:14px; border-radius:2px; object-fit:cover; }
.nuf-lang-btn i { font-size:10px; color:var(--gray); transition:transform var(--tr); }
.nuf-lang-btn.open i { transform:rotate(180deg); }
.nuf-lang-drop {
  position:absolute; top:calc(100% + 8px); right:0;
  background:white; border-radius:var(--r-lg);
  box-shadow:var(--sh-xl); border:1px solid var(--gray-lt);
  padding:6px; min-width:210px; max-height:380px; overflow-y:auto;
  opacity:0; visibility:hidden; transform:translateY(-8px);
  transition:var(--tr); z-index:2000;
}
.nuf-lang-drop.open { opacity:1; visibility:visible; transform:translateY(0); }
.nuf-lang-drop::-webkit-scrollbar { width:4px; }
.nuf-lang-drop::-webkit-scrollbar-thumb { background:var(--gray-lt); border-radius:2px; }
.nuf-lang-opt {
  display:flex; align-items:center; gap:10px;
  padding:9px 12px; border-radius:var(--r-sm);
  border:none; background:transparent; width:100%;
  text-align:left; cursor:pointer; font-family:'Inter',sans-serif;
  font-size:13px; font-weight:500; color:var(--dark);
  transition:var(--tr);
}
.nuf-lang-opt:hover { background:var(--primary-ltr); color:var(--primary); }
.nuf-lang-opt.active { background:var(--primary-ltr); color:var(--primary); font-weight:600; }
.nuf-lang-opt img { width:22px; height:15px; border-radius:2px; object-fit:cover; flex-shrink:0; }
.nuf-lang-sep { height:1px; background:var(--gray-lt); margin:4px 6px; }
.nuf-lang-label { padding:4px 12px 2px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--gray); }

/* ================================================================
   MAIN NAV — DESKTOP
================================================================ */
.nuf-nav {
  position:fixed; z-index:1020;
  top:calc(var(--h-top) + var(--h-header));
  left:0; right:0; height:var(--h-nav);
  background:white; border-bottom:1px solid var(--gray-lt);
  transition:top var(--tr), transform var(--tr-slow);
}
.nuf-header.scrolled ~ .nuf-nav { top:var(--h-header); }
.nuf-header.hide-up  ~ .nuf-nav { transform:translateY(calc(-100% - var(--h-header))); }
.nuf-nav-inner {
  max-width:1400px; margin:0 auto;
  height:100%; padding:0 20px;
  display:flex; align-items:center; justify-content:space-between; gap:12px;
}
.nuf-menu { display:flex; align-items:center; gap:2px; list-style:none; flex:1; overflow-x:auto; scrollbar-width:none; }
.nuf-menu::-webkit-scrollbar { display:none; }
.nuf-menu-item { position:relative; flex-shrink:0; }
.nuf-menu-link {
  display:flex; align-items:center; gap:5px;
  padding:9px 15px; border-radius:var(--r-sm);
  color:var(--dark); text-decoration:none;
  font-size:13px; font-weight:600; white-space:nowrap;
  border:2px solid transparent; background:transparent;
  cursor:pointer; transition:var(--tr); font-family:'Inter',sans-serif;
}
.nuf-menu-link:hover, .nuf-menu-link.active {
  background:var(--primary-ltr); color:var(--primary); border-color:var(--accent);
}
.nuf-menu-link i { font-size:11px; transition:transform var(--tr); }
.nuf-menu-item:hover > .nuf-menu-link i { transform:rotate(180deg); color:var(--accent); }

/* Dropdown */
.nuf-dropdown {
  position:absolute; top:calc(100% + 6px); left:0;
  background:white; border-radius:var(--r-xl);
  box-shadow:var(--sh-xl); border:1px solid var(--gray-lt);
  padding:10px; min-width:260px;
  opacity:0; visibility:hidden; transform:translateY(-8px);
  transition:var(--tr); z-index:500;
}
.nuf-menu-item:hover .nuf-dropdown { opacity:1; visibility:visible; transform:translateY(0); }
.nuf-drop-item {
  display:flex; align-items:center; gap:10px;
  padding:11px 12px; border-radius:var(--r-sm);
  color:var(--gray); text-decoration:none;
  font-size:13px; font-weight:500; transition:var(--tr);
}
.nuf-drop-item:hover { background:var(--primary-ltr); color:var(--primary); transform:translateX(3px); }
.nuf-drop-item i { width:18px; text-align:center; color:var(--primary); font-size:15px; }

/* Mega dropdown */
.nuf-mega { position:static!important; }
.nuf-mega-drop {
  position:absolute; top:calc(100% + 6px);
  left:50%; transform:translateX(-50%) translateY(-8px);
  width:min(92vw, 900px);
  background:white; border-radius:var(--r-xl);
  box-shadow:var(--sh-xl); border:1px solid var(--gray-lt);
  padding:28px; max-height:68vh; overflow-y:auto;
  opacity:0; visibility:hidden; transition:var(--tr); z-index:500;
}
.nuf-mega:hover .nuf-mega-drop { opacity:1; visibility:visible; transform:translateX(-50%) translateY(0); }
.nuf-mega-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:28px; }
.nuf-mega-col h3 {
  font-size:11px; font-weight:700; text-transform:uppercase;
  letter-spacing:1.2px; color:var(--primary);
  padding-bottom:10px; margin-bottom:12px;
  border-bottom:2px solid var(--accent);
  display:flex; align-items:center; gap:6px;
}
.nuf-mega-col h3 i { color:var(--accent); font-size:14px; }
.nuf-mega-list { list-style:none; }
.nuf-mega-list li { margin-bottom:2px; }
.nuf-mega-list a {
  display:flex; align-items:center; gap:7px;
  padding:9px 10px; border-radius:var(--r-sm);
  color:var(--gray); text-decoration:none;
  font-size:13px; font-weight:500; transition:var(--tr);
}
.nuf-mega-list a:hover { background:var(--primary-ltr); color:var(--primary); padding-left:14px; }
.nuf-mega-list a i { font-size:10px; color:var(--accent); }

/* CTA */
.nuf-nav-cta { flex-shrink:0; margin-left:12px; }
.nuf-btn-cta {
  padding:9px 18px;
  background:linear-gradient(135deg,var(--primary),var(--primary-lt));
  color:white; border-radius:var(--r-sm); text-decoration:none;
  font-size:13px; font-weight:600; font-family:'Inter',sans-serif;
  display:flex; align-items:center; gap:7px; white-space:nowrap;
  box-shadow:0 3px 10px rgba(15,76,58,.25);
  transition:var(--tr); border:none; cursor:pointer;
}
.nuf-btn-cta:hover { transform:translateY(-2px); box-shadow:0 5px 18px rgba(15,76,58,.35); color:white; }

/* ================================================================
   MOBILE NAV PANEL
================================================================ */
.nuf-overlay {
  position:fixed; inset:0; z-index:2040;
  background:rgba(15,76,58,.55); backdrop-filter:blur(4px);
  opacity:0; visibility:hidden; transition:var(--tr);
}
.nuf-overlay.open { opacity:1; visibility:visible; }
.nuf-panel {
  position:fixed; top:0; left:-100%; bottom:0;
  width:min(340px,88vw); z-index:2050;
  background:white; overflow-y:auto;
  display:flex; flex-direction:column;
  box-shadow:var(--sh-xl);
  transition:left var(--tr-slow);
}
.nuf-panel.open { left:0; }
.nuf-panel-head {
  padding:20px 18px 18px;
  background:var(--primary); color:white;
  position:sticky; top:0; z-index:10; flex-shrink:0;
}
.nuf-panel-close {
  position:absolute; top:14px; right:14px;
  width:34px; height:34px; border-radius:50%;
  background:rgba(255,255,255,.15); border:none; color:white;
  font-size:18px; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  transition:var(--tr);
}
.nuf-panel-close:hover { background:rgba(255,255,255,.25); transform:rotate(90deg); }
.nuf-panel-user { display:flex; align-items:center; gap:11px; margin-top:6px; }
.nuf-panel-avatar {
  width:48px; height:48px; border-radius:50%;
  border:3px solid var(--accent); object-fit:cover;
  background:var(--accent); color:var(--primary-dk);
  font-size:16px; font-weight:700;
  display:flex; align-items:center; justify-content:center;
}
.nuf-panel-userinfo h4 { font-size:15px; font-weight:600; margin:0; }
.nuf-panel-userinfo p { font-size:12px; opacity:.85; margin:3px 0 0; }
.nuf-panel-body { flex:1; padding:14px; }
.nuf-panel-section { margin-bottom:22px; }
.nuf-panel-sec-title {
  font-size:10px; font-weight:700; text-transform:uppercase;
  letter-spacing:.1em; color:var(--gray);
  padding:0 10px; margin-bottom:8px;
  display:flex; align-items:center; gap:5px;
}
.nuf-panel-list { list-style:none; }
.nuf-panel-link {
  display:flex; align-items:center; gap:11px;
  padding:13px 10px; border-radius:var(--r-md);
  color:var(--dark); text-decoration:none;
  font-size:15px; font-weight:500; cursor:pointer;
  background:transparent; border:none; width:100%; text-align:left;
  transition:var(--tr);
}
.nuf-panel-link:hover, .nuf-panel-link.active { background:var(--primary-ltr); color:var(--primary); }
.nuf-panel-link i { font-size:19px; color:var(--primary); width:22px; text-align:center; flex-shrink:0; }
.nuf-panel-link .ch { margin-left:auto; font-size:12px; transition:transform var(--tr); }
.nuf-panel-link.open .ch { transform:rotate(90deg); }
.nuf-sub { max-height:0; overflow:hidden; padding-left:44px; transition:max-height .35s ease; }
.nuf-sub.open { max-height:600px; }
.nuf-sub-item {
  display:flex; align-items:center; gap:8px;
  padding:11px 12px; color:var(--gray); text-decoration:none;
  font-size:14px; border-left:2px solid var(--gray-lt);
  transition:var(--tr); background:transparent; border-top:none; border-right:none; border-bottom:none;
  width:100%; text-align:left; cursor:pointer; font-family:'Inter',sans-serif;
}
.nuf-sub-item:hover { color:var(--primary); border-left-color:var(--accent); padding-left:16px; }
.nuf-sub-item img { width:22px; height:15px; border-radius:2px; object-fit:cover; flex-shrink:0; }
.nuf-panel-foot {
  padding:16px; border-top:1px solid var(--gray-lt);
  background:var(--light); flex-shrink:0;
}
.nuf-panel-btn {
  display:block; width:100%; padding:13px;
  text-align:center; border-radius:var(--r-md);
  font-weight:600; font-size:14px; text-decoration:none;
  transition:var(--tr); margin-bottom:10px;
  border:none; cursor:pointer; font-family:'Inter',sans-serif;
}
.nuf-panel-btn.primary { background:var(--primary); color:white; }
.nuf-panel-btn.primary:hover { background:var(--primary-lt); transform:translateY(-1px); }
.nuf-panel-btn.outline { background:transparent; color:var(--primary); border:2px solid var(--primary); }
.nuf-panel-btn.outline:hover { background:var(--primary); color:white; }

/* ================================================================
   BOTTOM NAV — MOBILE
================================================================ */
.nuf-bottom {
  display:none; position:fixed; bottom:0; left:0; right:0; z-index:1035;
  height:var(--h-bottom); background:white;
  border-top:1px solid var(--gray-lt);
  box-shadow:0 -3px 12px rgba(0,0,0,.06);
}
.nuf-bottom-list {
  display:flex; justify-content:space-around; align-items:center;
  height:100%; list-style:none; padding:0 4px;
}
.nuf-bottom-link {
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  gap:3px; padding:6px 8px; border-radius:var(--r-md);
  color:var(--gray); text-decoration:none;
  font-size:10px; font-weight:500; transition:var(--tr);
  min-width:52px; position:relative;
}
.nuf-bottom-link:hover, .nuf-bottom-link.active { color:var(--primary); background:var(--primary-ltr); }
.nuf-bottom-link i { font-size:22px; }

/* ================================================================
   LOADER
================================================================ */
.nuf-loader {
  position:fixed; inset:0; z-index:9999;
  background:white;
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  gap:18px;
}
.nuf-loader-ring {
  width:100px; height:100px; position:relative;
}
.nuf-loader-ring-track {
  width:100%; height:100%; border-radius:50%;
  border:4px solid var(--gray-lt);
  border-top-color:var(--accent);
  animation:nuf-spin 1s linear infinite;
}
.nuf-loader-logo {
  position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
  width:58px; height:58px; border-radius:50%; object-fit:cover;
}
.nuf-loader-text {
  font-family:'Playfair Display',serif;
  font-weight:700; font-size:1.15rem;
  color:var(--primary); letter-spacing:.12em; text-transform:uppercase;
}
.nuf-loader.fade-out { opacity:0; pointer-events:none; transition:opacity .4s ease; }
@keyframes nuf-spin { to { transform:rotate(360deg); } }

/* ================================================================
   RESPONSIVE
================================================================ */
@media (max-width:1200px) {
  .nuf-search { max-width:320px; }
  .nuf-menu-link { padding:9px 12px; font-size:12px; }
  .nuf-brand-text h1 { font-size:17px; }
}
@media (max-width:992px) {
  :root { --h-top:0px; --h-header:62px; --h-nav:0px; }
  body { padding-top:var(--h-header); padding-bottom:var(--h-bottom); }
  .nuf-topbar { display:none!important; }
  .nuf-header { top:0!important; box-shadow:var(--sh-sm); }
  .nuf-header.hide-up { transform:translateY(-100%); }
  .nuf-nav { display:none!important; }
  .nuf-hamburger { display:flex; }
  .nuf-lang { display:none!important; }
  .nuf-action-btn span { display:none; }
  .nuf-action-btn { padding:8px; }
  .nuf-bottom { display:block; }
  /* Search mobile: hidden until toggled */
  .nuf-search {
    position:absolute; top:calc(var(--h-header) + 6px);
    left:10px; right:10px; max-width:none;
    opacity:0; visibility:hidden; transform:translateY(-8px);
    z-index:1025;
  }
  .nuf-search.show { opacity:1; visibility:visible; transform:translateY(0); }
  .nuf-search-input { height:46px; box-shadow:var(--sh-md); border-color:var(--primary); }
}
@media (max-width:576px) {
  :root { --h-header:58px; --h-bottom:58px; }
  .nuf-header-inner { padding:0 12px; gap:8px; }
  .nuf-brand-text span { display:none; }
  .nuf-brand-text h1 { font-size:15px; }
  .nuf-brand-logo { width:38px; height:38px; }
  .nuf-actions { gap:2px; }
  .nuf-hamburger { width:36px; height:36px; font-size:20px; }
  .nuf-panel { width:min(300px,93vw); }
  .nuf-bottom-link { font-size:9px; min-width:44px; }
  .nuf-bottom-link i { font-size:20px; }
}
@media (max-width:360px) {
  .nuf-brand-text h1 { font-size:13px; }
}
@media (prefers-reduced-motion:reduce) {
  * { animation-duration:.01ms!important; transition-duration:.01ms!important; }
}
@media print {
  .nuf-topbar,.nuf-header,.nuf-nav,.nuf-bottom,.nuf-panel,.nuf-overlay,.nuf-loader { display:none!important; }
  body { padding:0!important; }
}
</style>
</head>
<body>

<!-- LOADER -->
<div class="nuf-loader" id="nufLoader">
  <div class="nuf-loader-ring">
    <div class="nuf-loader-ring-track"></div>
    <img class="nuf-loader-logo"
         src="<?= base_url('attachments/Configurations/'.$this->Model->get_setting('site_logo','assets/fro.png')) ?>"
         alt="NUFOTEC" onerror="this.src='<?= base_url('assets/images/logo.png') ?>'">
  </div>
  <div class="nuf-loader-text"><?= $this->Model->get_setting('site_name','NUFOTEC BURUNDI') ?></div>
</div>

<!-- GOOGLE TRANSLATE (caché) -->
<div id="google_translate_element" style="display:none;"></div>
<script>
function googleTranslateElementInit(){
  new google.translate.TranslateElement({
    pageLanguage:'fr',
    includedLanguages:'fr,en,zh-CN,es,hi,ar,pt,bn,ru,ja,de,vi,tr,ko,it,fa,pl,ro,nl,uk,ms,id,th,sv,fi,da,no,cs,hu,el,he,rn,sw,so,am,yo,ig,ha,zu,af,ta,te,mr,kn,gu,pa,ur,ne,km',
    layout:google.translate.TranslateElement.InlineLayout.SIMPLE,
    autoDisplay:false
  },'google_translate_element');
}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>

<!-- TOP BAR -->
<div class="nuf-topbar" id="nufTopbar">
  <div class="nuf-topbar-inner">
    <div class="nuf-topbar-side">
      <a href="tel:<?= $this->Model->get_setting('site_phone','+257 79 666 439') ?>">
        <i class="bi bi-telephone-fill"></i>
        <span class="d-none d-md-inline"><?= $this->Model->get_setting('site_phone','+257 79 666 439') ?></span>
      </a>
      <div class="nuf-topbar-div d-none d-sm-block"></div>
      <a href="mailto:<?= $this->Model->get_setting('contact_email_invest','nufotecburundi2026@gmail.com') ?>">
        <i class="bi bi-envelope-fill"></i>
        <span class="d-none d-lg-inline"><?= $this->Model->get_setting('contact_email_invest','nufotecburundi2026@gmail.com') ?></span>
      </a>
    </div>
    <div class="nuf-topbar-side">
      <a href="#">
        <i class="bi bi-geo-alt-fill"></i>
        <span class="d-none d-md-inline"><?= $this->Model->get_setting('adresse_siege','Bujumbura, Burundi') ?></span>
      </a>
      <div class="nuf-topbar-div d-none d-sm-block"></div>
      <a href="#">
        <i class="bi bi-clock-fill"></i>
        <span class="d-none d-sm-inline"><?= $this->Model->get_setting('horaires_travail','Lun-Ven: 8h-17h') ?></span>
      </a>
    </div>
  </div>
</div>

<!-- MAIN HEADER -->
<header class="nuf-header" id="nufHeader">
  <div class="nuf-header-inner">

    <!-- Brand -->
    <a href="<?= base_url() ?>" class="nuf-brand">
      <div class="nuf-brand-logo">
        <img src="<?= base_url('attachments/Configurations/'.$this->Model->get_setting('site_logo','logo.png')) ?>"
             alt="NUFOTEC" onerror="this.src='<?= base_url('assets/images/logo.png') ?>'">
      </div>
      <div class="nuf-brand-text">
        <h1><?= $this->Model->get_setting('site_name','NUFOTEC BURUNDI') ?></h1>
        <span><?= $this->Model->get_setting('span_site_name','Natural Health') ?></span>
      </div>
    </a>

    <!-- Search -->
    <div class="nuf-search" id="nufSearch">
      <input type="text" id="nufSearchInput" class="nuf-search-input"
             placeholder="Rechercher produits, pages..." autocomplete="off">
      <button class="nuf-search-btn" id="nufSearchBtn" aria-label="Rechercher">
        <i class="bi bi-search"></i>
      </button>
      <div class="nuf-search-results" id="nufSearchResults"></div>
    </div>

    <!-- Actions -->
    <div class="nuf-actions">

      <!-- Search toggle (mobile only) -->
      <button class="nuf-action-btn d-lg-none" id="nufSearchToggle" aria-label="Rechercher">
        <i class="bi bi-search"></i>
      </button>

      <!-- Account -->
      <a href="<?= $logged_in ? base_url('home-patient') : base_url('auth') ?>"
         class="nuf-action-btn d-none d-lg-flex"
         title="<?= $logged_in ? 'Mon compte' : 'Connexion' ?>">
        <?php if ($logged_in && !empty($user_photo) && file_exists(FCPATH.'attachments/Users/'.$user_photo)): ?>
          <img src="<?= base_url('attachments/Users/'.$user_photo) ?>" alt="Avatar" class="nuf-avatar">
        <?php elseif ($logged_in): ?>
          <div class="nuf-initials"><?= $initials ?></div>
        <?php else: ?>
          <i class="bi bi-person-circle"></i>
        <?php endif; ?>
        <span class="d-none d-xl-inline"><?= $logged_in ? 'Mon compte' : 'Connexion' ?></span>
      </a>

      <!-- Language selector (desktop) -->
      <div class="nuf-lang d-none d-lg-block" id="nufLangWrap">
        <button class="nuf-lang-btn" id="nufLangBtn">
          <img src="" alt="" id="nufLangFlag">
          <span id="nufLangLabel">Français</span>
          <i class="bi bi-chevron-down"></i>
        </button>
        <div class="nuf-lang-drop" id="nufLangDrop">
          <div class="nuf-lang-label">Langues principales</div>
          <!-- Les 50 langues les plus parlées au monde, rangées par nombre de locuteurs -->
          <button class="nuf-lang-opt" data-lang="fr"    data-flag="fr" data-name="Français" data-default="1"><img src="https://flagcdn.com/w20/fr.png" alt=""> Français</button>

          <button class="nuf-lang-opt" data-lang="en"    data-flag="gb" data-name="English"><img src="https://flagcdn.com/w20/gb.png" alt=""> English — Anglais</button>

          <button class="nuf-lang-opt" data-lang="sw"    data-flag="tz" data-name="Kiswahili"><img src="https://flagcdn.com/w20/tz.png" alt=""> Kiswahili — Swahili</button>
          <button class="nuf-lang-opt" data-lang="rn"    data-flag="bi" data-name="Kirundi"><img src="https://flagcdn.com/w20/bi.png" alt=""> Kirundi — Rundi</button>

          <button class="nuf-lang-opt" data-lang="zh-CN" data-flag="cn" data-name="中文 (Chinois)"><img src="https://flagcdn.com/w20/cn.png" alt=""> 中文 — Chinois</button>
          <button class="nuf-lang-opt" data-lang="es"    data-flag="es" data-name="Español"><img src="https://flagcdn.com/w20/es.png" alt=""> Español — Espagnol</button>
          
          <button class="nuf-lang-opt" data-lang="hi"    data-flag="in" data-name="हिन्दी"><img src="https://flagcdn.com/w20/in.png" alt=""> हिन्दी — Hindi</button>
          <button class="nuf-lang-opt" data-lang="ar"    data-flag="sa" data-name="العربية"><img src="https://flagcdn.com/w20/sa.png" alt=""> العربية — Arabe</button>
          
          <button class="nuf-lang-opt" data-lang="bn"    data-flag="bd" data-name="বাংলা"><img src="https://flagcdn.com/w20/bd.png" alt=""> বাংলা — Bengali</button>
          <button class="nuf-lang-opt" data-lang="pt"    data-flag="pt" data-name="Português"><img src="https://flagcdn.com/w20/pt.png" alt=""> Português — Portugais</button>
          <button class="nuf-lang-opt" data-lang="ru"    data-flag="ru" data-name="Русский"><img src="https://flagcdn.com/w20/ru.png" alt=""> Русский — Russe</button>
          <button class="nuf-lang-opt" data-lang="ur"    data-flag="pk" data-name="اردو"><img src="https://flagcdn.com/w20/pk.png" alt=""> اردو — Ourdou</button>
          <div class="nuf-lang-sep"></div>
          <div class="nuf-lang-label">Autres langues</div>
          <button class="nuf-lang-opt" data-lang="id"    data-flag="id" data-name="Bahasa Indonesia"><img src="https://flagcdn.com/w20/id.png" alt=""> Bahasa Indonesia</button>
          <button class="nuf-lang-opt" data-lang="de"    data-flag="de" data-name="Deutsch"><img src="https://flagcdn.com/w20/de.png" alt=""> Deutsch — Allemand</button>
          <button class="nuf-lang-opt" data-lang="ja"    data-flag="jp" data-name="日本語"><img src="https://flagcdn.com/w20/jp.png" alt=""> 日本語 — Japonais</button>
          <button class="nuf-lang-opt" data-lang="ms"    data-flag="my" data-name="Bahasa Melayu"><img src="https://flagcdn.com/w20/my.png" alt=""> Bahasa Melayu — Malais</button>
          
          <button class="nuf-lang-opt" data-lang="tr"    data-flag="tr" data-name="Türkçe"><img src="https://flagcdn.com/w20/tr.png" alt=""> Türkçe — Turc</button>
          <button class="nuf-lang-opt" data-lang="ko"    data-flag="kr" data-name="한국어"><img src="https://flagcdn.com/w20/kr.png" alt=""> 한국어 — Coréen</button>
          <button class="nuf-lang-opt" data-lang="vi"    data-flag="vn" data-name="Tiếng Việt"><img src="https://flagcdn.com/w20/vn.png" alt=""> Tiếng Việt — Vietnamien</button>
          <button class="nuf-lang-opt" data-lang="it"    data-flag="it" data-name="Italiano"><img src="https://flagcdn.com/w20/it.png" alt=""> Italiano — Italien</button>
          <button class="nuf-lang-opt" data-lang="fa"    data-flag="ir" data-name="فارسی"><img src="https://flagcdn.com/w20/ir.png" alt=""> فارسی — Persan</button>
          <button class="nuf-lang-opt" data-lang="ta"    data-flag="lk" data-name="தமிழ்"><img src="https://flagcdn.com/w20/lk.png" alt=""> தமிழ் — Tamoul</button>
          <button class="nuf-lang-opt" data-lang="th"    data-flag="th" data-name="ภาษาไทย"><img src="https://flagcdn.com/w20/th.png" alt=""> ภาษาไทย — Thaï</button>
          <button class="nuf-lang-opt" data-lang="pl"    data-flag="pl" data-name="Polski"><img src="https://flagcdn.com/w20/pl.png" alt=""> Polski — Polonais</button>
          <button class="nuf-lang-opt" data-lang="nl"    data-flag="nl" data-name="Nederlands"><img src="https://flagcdn.com/w20/nl.png" alt=""> Nederlands — Néerlandais</button>
          <button class="nuf-lang-opt" data-lang="uk"    data-flag="ua" data-name="Українська"><img src="https://flagcdn.com/w20/ua.png" alt=""> Українська — Ukrainien</button>
          <button class="nuf-lang-opt" data-lang="ro"    data-flag="ro" data-name="Română"><img src="https://flagcdn.com/w20/ro.png" alt=""> Română — Roumain</button>
          <button class="nuf-lang-opt" data-lang="el"    data-flag="gr" data-name="Ελληνικά"><img src="https://flagcdn.com/w20/gr.png" alt=""> Ελληνικά — Grec</button>
          <button class="nuf-lang-opt" data-lang="cs"    data-flag="cz" data-name="Čeština"><img src="https://flagcdn.com/w20/cz.png" alt=""> Čeština — Tchèque</button>
          <button class="nuf-lang-opt" data-lang="hu"    data-flag="hu" data-name="Magyar"><img src="https://flagcdn.com/w20/hu.png" alt=""> Magyar — Hongrois</button>
          <button class="nuf-lang-opt" data-lang="sv"    data-flag="se" data-name="Svenska"><img src="https://flagcdn.com/w20/se.png" alt=""> Svenska — Suédois</button>
          <button class="nuf-lang-opt" data-lang="he"    data-flag="il" data-name="עברית"><img src="https://flagcdn.com/w20/il.png" alt=""> עברית — Hébreu</button>
          <button class="nuf-lang-opt" data-lang="da"    data-flag="dk" data-name="Dansk"><img src="https://flagcdn.com/w20/dk.png" alt=""> Dansk — Danois</button>
          <button class="nuf-lang-opt" data-lang="no"    data-flag="no" data-name="Norsk"><img src="https://flagcdn.com/w20/no.png" alt=""> Norsk — Norvégien</button>
          <button class="nuf-lang-opt" data-lang="fi"    data-flag="fi" data-name="Suomi"><img src="https://flagcdn.com/w20/fi.png" alt=""> Suomi — Finnois</button>
          <button class="nuf-lang-opt" data-lang="am"    data-flag="et" data-name="አማርኛ"><img src="https://flagcdn.com/w20/et.png" alt=""> አማርኛ — Amharique</button>
          <button class="nuf-lang-opt" data-lang="so"    data-flag="so" data-name="Soomaali"><img src="https://flagcdn.com/w20/so.png" alt=""> Soomaali — Somali</button>
          <button class="nuf-lang-opt" data-lang="yo"    data-flag="ng" data-name="Yorùbá"><img src="https://flagcdn.com/w20/ng.png" alt=""> Yorùbá — Yoruba</button>
          <button class="nuf-lang-opt" data-lang="ha"    data-flag="ng" data-name="Hausa"><img src="https://flagcdn.com/w20/ng.png" alt=""> Hausa</button>
          <button class="nuf-lang-opt" data-lang="ig"    data-flag="ng" data-name="Igbo"><img src="https://flagcdn.com/w20/ng.png" alt=""> Igbo</button>
          <button class="nuf-lang-opt" data-lang="zu"    data-flag="za" data-name="isiZulu"><img src="https://flagcdn.com/w20/za.png" alt=""> isiZulu — Zoulou</button>
          <button class="nuf-lang-opt" data-lang="af"    data-flag="za" data-name="Afrikaans"><img src="https://flagcdn.com/w20/za.png" alt=""> Afrikaans</button>
          <button class="nuf-lang-opt" data-lang="km"    data-flag="kh" data-name="ខ្មែរ"><img src="https://flagcdn.com/w20/kh.png" alt=""> ខ្មែរ — Khmer</button>
          <button class="nuf-lang-opt" data-lang="ne"    data-flag="np" data-name="नेपाली"><img src="https://flagcdn.com/w20/np.png" alt=""> नेपाली — Népalais</button>
          <button class="nuf-lang-opt" data-lang="mr"    data-flag="in" data-name="मराठी"><img src="https://flagcdn.com/w20/in.png" alt=""> मराठी — Marathi</button>
          <button class="nuf-lang-opt" data-lang="te"    data-flag="in" data-name="తెలుగు"><img src="https://flagcdn.com/w20/in.png" alt=""> తెలుగు — Télougou</button>
          <button class="nuf-lang-opt" data-lang="kn"    data-flag="in" data-name="ಕನ್ನಡ"><img src="https://flagcdn.com/w20/in.png" alt=""> ಕನ್ನಡ — Kannada</button>
          <button class="nuf-lang-opt" data-lang="gu"    data-flag="in" data-name="ગુજરાતી"><img src="https://flagcdn.com/w20/in.png" alt=""> ગુજરાતી — Gujarati</button>
          <button class="nuf-lang-opt" data-lang="pa"    data-flag="pk" data-name="ਪੰਜਾਬੀ"><img src="https://flagcdn.com/w20/pk.png" alt=""> ਪੰਜਾਬੀ — Pendjabi</button>
        </div>
      </div>

      <!-- Hamburger -->
      <button class="nuf-hamburger" id="nufHamburger" aria-label="Menu">
        <i class="bi bi-list"></i>
      </button>
    </div><!-- end .nuf-actions -->

  </div>
</header>

<!-- DESKTOP NAV -->
<nav class="nuf-nav" id="nufNav">
  <div class="nuf-nav-inner">
    <ul class="nuf-menu">

      <li class="nuf-menu-item">
        <a href="<?= base_url('') ?>" class="nuf-menu-link">
          <?= isset($t) ? $t('home') : 'Accueil' ?>
        </a>
      </li>

      <li class="nuf-menu-item nuf-mega">
        <a href="#" class="nuf-menu-link">
          <?= isset($t) ? $t('about') : 'À propos' ?>
          <i class="bi bi-chevron-down"></i>
        </a>
        <div class="nuf-mega-drop">
          <div class="nuf-mega-grid">
            <div class="nuf-mega-col">
              <h3><i class="bi bi-building"></i> Entreprise</h3>
              <ul class="nuf-mega-list">
                <li><a href="<?= base_url('background-strategic-rationale') ?>"><i class="bi bi-chevron-right"></i> Contexte stratégique</a></li>
                <li><a href="<?= base_url('corporate-structure-governance') ?>"><i class="bi bi-chevron-right"></i> Gouvernance</a></li>
                <li><a href="<?= base_url('vision-mission') ?>"><i class="bi bi-chevron-right"></i> Vision &amp; Mission</a></li>
              </ul>
            </div>
            <div class="nuf-mega-col">
              <h3><i class="bi bi-leaf"></i> Durabilité</h3>
              <ul class="nuf-mega-list">
                <li><a href="<?= base_url('esg_Sustainability') ?>"><i class="bi bi-chevron-right"></i> ESG &amp; Durabilité</a></li>
                <li><a href="<?= base_url('risk-analysis') ?>"><i class="bi bi-chevron-right"></i> Analyse des risques</a></li>
                <li><a href="<?= base_url('Research_Innovation') ?>"><i class="bi bi-chevron-right"></i> Innovation</a></li>
                <li><a href="<?= base_url('market-outlook') ?>"><i class="bi bi-chevron-right"></i> Perspectives marché</a></li>
              </ul>
            </div>
            <div class="nuf-mega-col">
              <h3><i class="bi bi-gear-wide-connected"></i> Installations</h3>
              <ul class="nuf-mega-list">
                <li><a href="<?= base_url('nufotec-phytomed-facility') ?>"><i class="bi bi-chevron-right"></i> Unité PhytoMed</a></li>
                <li><a href="<?= base_url('digital-growth') ?>"><i class="bi bi-chevron-right"></i> Croissance digitale</a></li>
              </ul>
            </div>
          </div>
        </div>
      </li>

      <li class="nuf-menu-item">
        <a href="<?= base_url('Products') ?>" class="nuf-menu-link">
          <?= isset($t) ? $t('shop') : 'Boutique' ?>
        </a>
      </li>

      <li class="nuf-menu-item">
        <a href="<?= base_url('Medicins') ?>" class="nuf-menu-link">
          <?= isset($t) ? $t('teleconsultation') : 'Téléconsultation' ?>
        </a>
      </li>

      <li class="nuf-menu-item nuf-mega">
        <a href="#" class="nuf-menu-link">
          <?= isset($t) ? $t('investment') : 'Investissement' ?>
          <i class="bi bi-chevron-down"></i>
        </a>
        <div class="nuf-mega-drop">
          <div class="nuf-mega-grid">
            <div class="nuf-mega-col">
              <h3><i class="bi bi-handshake"></i> Partenariats</h3>
              <ul class="nuf-mega-list">
                <li><a href="<?= base_url('investment-projection') ?>"><i class="bi bi-chevron-right"></i> Projections investissement</a></li>
                <li><a href="<?= base_url('investor-commitment') ?>"><i class="bi bi-chevron-right"></i> Engagement investisseur</a></li>
                <li><a href="<?= base_url('strategic-partnerships') ?>"><i class="bi bi-chevron-right"></i> Partenariats stratégiques</a></li>
              </ul>
            </div>
            <div class="nuf-mega-col">
              <h3><i class="bi bi-bank"></i> Relations</h3>
              <ul class="nuf-mega-list">
                <li><a href="<?= base_url('broker-commission') ?>"><i class="bi bi-chevron-right"></i> Commission courtier</a></li>
              </ul>
            </div>
          </div>
        </div>
      </li>

      <li class="nuf-menu-item">
        <a href="<?= base_url('media') ?>" class="nuf-menu-link">
          <?= isset($t) ? $t('media') : 'Médias' ?>
        </a>
      </li>

    </ul>
    <div class="nuf-nav-cta">
      <a href="<?= base_url('Home/Contact') ?>" class="nuf-btn-cta">
        <i class="bi bi-headset"></i>
        <?= isset($t) ? $t('contact') : 'Contact' ?>
      </a>
    </div>
  </div>
</nav>

<!-- MOBILE OVERLAY + PANEL -->
<div class="nuf-overlay" id="nufOverlay"></div>
<div class="nuf-panel" id="nufPanel">

  <div class="nuf-panel-head">
    <button class="nuf-panel-close" id="nufPanelClose"><i class="bi bi-x-lg"></i></button>
    <div class="nuf-panel-user">
      <?php if ($logged_in && !empty($user_photo) && file_exists(FCPATH.'attachments/Users/'.$user_photo)): ?>
        <img src="<?= base_url('attachments/Users/'.$user_photo) ?>" alt="Avatar" class="nuf-panel-avatar">
      <?php else: ?>
        <div class="nuf-panel-avatar"><?= $initials ?></div>
      <?php endif; ?>
      <div class="nuf-panel-userinfo">
        <h4><?= $logged_in ? htmlspecialchars($user_name) : 'Bienvenue' ?></h4>
        <p><?= $logged_in ? 'Connecté' : 'Connectez-vous à votre compte' ?></p>
      </div>
    </div>
  </div>

  <div class="nuf-panel-body">

    <!-- Navigation -->
    <div class="nuf-panel-section">
      <div class="nuf-panel-sec-title"><i class="bi bi-grid-3x3-gap"></i> Navigation</div>
      <ul class="nuf-panel-list">
        <li><a href="<?= base_url() ?>" class="nuf-panel-link"><i class="bi bi-house-door"></i><span>Accueil</span></a></li>
        <li>
          <button class="nuf-panel-link" data-sub="mob-about">
            <i class="bi bi-building"></i><span>À propos</span><i class="bi bi-chevron-right ch"></i>
          </button>
          <div class="nuf-sub" id="nuf-sub-mob-about">
            <a href="<?= base_url('vision-mission') ?>" class="nuf-sub-item">Vision &amp; Mission</a>
            <a href="<?= base_url('corporate-structure-governance') ?>" class="nuf-sub-item">Gouvernance</a>
            <a href="<?= base_url('esg_Sustainability') ?>" class="nuf-sub-item">Durabilité ESG</a>
          </div>
        </li>
        <li><a href="<?= base_url('Products') ?>" class="nuf-panel-link"><i class="bi bi-box-seam"></i><span>Boutique</span></a></li>
        <li><a href="<?= base_url('Medicins') ?>" class="nuf-panel-link"><i class="bi bi-camera-video"></i><span>Téléconsultation</span></a></li>
        <li>
          <button class="nuf-panel-link" data-sub="mob-invest">
            <i class="bi bi-bar-chart-line"></i><span>Investissement</span><i class="bi bi-chevron-right ch"></i>
          </button>
          <div class="nuf-sub" id="nuf-sub-mob-invest">
            <a href="<?= base_url('investment-projection') ?>" class="nuf-sub-item">Projections</a>
            <a href="<?= base_url('strategic-partnerships') ?>" class="nuf-sub-item">Partenariats</a>
            <a href="<?= base_url('broker-commission') ?>" class="nuf-sub-item">Commission courtier</a>
          </div>
        </li>
        <li><a href="<?= base_url('media') ?>" class="nuf-panel-link"><i class="bi bi-collection-play"></i><span>Médias</span></a></li>
        <li><a href="<?= base_url('Home/Contact') ?>" class="nuf-panel-link"><i class="bi bi-envelope"></i><span>Contact</span></a></li>
      </ul>
    </div>

    <!-- Language selector mobile -->
    <div class="nuf-panel-section">
      <div class="nuf-panel-sec-title"><i class="bi bi-globe"></i> Langue</div>
      <ul class="nuf-panel-list">
        <li>
          <button class="nuf-panel-link" data-sub="mob-lang">
            <i class="bi bi-translate"></i><span>Changer de langue</span><i class="bi bi-chevron-right ch"></i>
          </button>
          <div class="nuf-sub" id="nuf-sub-mob-lang" style="max-height:0;overflow-y:auto;">

            <button class="nuf-sub-item" data-lang="fr"    data-flag="fr" data-name="Français" data-default="1"><img src="https://flagcdn.com/w20/fr.png" alt=""> Français</button>

            <button class="nuf-sub-item" data-lang="en"    data-flag="gb" data-name="English"><img src="https://flagcdn.com/w20/gb.png" alt=""> English — Anglais</button>

            <button class="nuf-sub-item" data-lang="sw"    data-flag="tz" data-name="Kiswahili"><img src="https://flagcdn.com/w20/tz.png" alt=""> Kiswahili</button>
            <button class="nuf-sub-item" data-lang="rn"    data-flag="bi" data-name="Kirundi"><img src="https://flagcdn.com/w20/bi.png" alt=""> Kirundi</button>

            <button class="nuf-sub-item" data-lang="zh-CN" data-flag="cn" data-name="中文"><img src="https://flagcdn.com/w20/cn.png" alt=""> 中文 — Chinois</button>
            <button class="nuf-sub-item" data-lang="es"    data-flag="es" data-name="Español"><img src="https://flagcdn.com/w20/es.png" alt=""> Español — Espagnol</button>
            
            <button class="nuf-sub-item" data-lang="hi"    data-flag="in" data-name="हिन्दी"><img src="https://flagcdn.com/w20/in.png" alt=""> हिन्दी — Hindi</button>
            <button class="nuf-sub-item" data-lang="ar"    data-flag="sa" data-name="العربية"><img src="https://flagcdn.com/w20/sa.png" alt=""> العربية — Arabe</button>
            
            <button class="nuf-sub-item" data-lang="bn"    data-flag="bd" data-name="বাংলা"><img src="https://flagcdn.com/w20/bd.png" alt=""> বাংলা — Bengali</button>
            <button class="nuf-sub-item" data-lang="pt"    data-flag="pt" data-name="Português"><img src="https://flagcdn.com/w20/pt.png" alt=""> Português</button>
            <button class="nuf-sub-item" data-lang="ru"    data-flag="ru" data-name="Русский"><img src="https://flagcdn.com/w20/ru.png" alt=""> Русский — Russe</button>
            <button class="nuf-sub-item" data-lang="id"    data-flag="id" data-name="Bahasa Indonesia"><img src="https://flagcdn.com/w20/id.png" alt=""> Bahasa Indonesia</button>
            <button class="nuf-sub-item" data-lang="de"    data-flag="de" data-name="Deutsch"><img src="https://flagcdn.com/w20/de.png" alt=""> Deutsch — Allemand</button>
            <button class="nuf-sub-item" data-lang="ja"    data-flag="jp" data-name="日本語"><img src="https://flagcdn.com/w20/jp.png" alt=""> 日本語 — Japonais</button>
            
            <button class="nuf-sub-item" data-lang="tr"    data-flag="tr" data-name="Türkçe"><img src="https://flagcdn.com/w20/tr.png" alt=""> Türkçe — Turc</button>
            <button class="nuf-sub-item" data-lang="ko"    data-flag="kr" data-name="한국어"><img src="https://flagcdn.com/w20/kr.png" alt=""> 한국어 — Coréen</button>
            <button class="nuf-sub-item" data-lang="vi"    data-flag="vn" data-name="Tiếng Việt"><img src="https://flagcdn.com/w20/vn.png" alt=""> Tiếng Việt</button>
            <button class="nuf-sub-item" data-lang="it"    data-flag="it" data-name="Italiano"><img src="https://flagcdn.com/w20/it.png" alt=""> Italiano — Italien</button>
            <button class="nuf-sub-item" data-lang="ms"    data-flag="my" data-name="Bahasa Melayu"><img src="https://flagcdn.com/w20/my.png" alt=""> Bahasa Melayu</button>
            <button class="nuf-sub-item" data-lang="th"    data-flag="th" data-name="ภาษาไทย"><img src="https://flagcdn.com/w20/th.png" alt=""> ภาษาไทย — Thaï</button>
            <button class="nuf-sub-item" data-lang="fa"    data-flag="ir" data-name="فارسی"><img src="https://flagcdn.com/w20/ir.png" alt=""> فارسی — Persan</button>
            <button class="nuf-sub-item" data-lang="pl"    data-flag="pl" data-name="Polski"><img src="https://flagcdn.com/w20/pl.png" alt=""> Polski — Polonais</button>
            <button class="nuf-sub-item" data-lang="nl"    data-flag="nl" data-name="Nederlands"><img src="https://flagcdn.com/w20/nl.png" alt=""> Nederlands</button>
            <button class="nuf-sub-item" data-lang="uk"    data-flag="ua" data-name="Українська"><img src="https://flagcdn.com/w20/ua.png" alt=""> Українська</button>
            <button class="nuf-sub-item" data-lang="el"    data-flag="gr" data-name="Ελληνικά"><img src="https://flagcdn.com/w20/gr.png" alt=""> Ελληνικά — Grec</button>
            <button class="nuf-sub-item" data-lang="he"    data-flag="il" data-name="עברית"><img src="https://flagcdn.com/w20/il.png" alt=""> עברית — Hébreu</button>
            <button class="nuf-sub-item" data-lang="am"    data-flag="et" data-name="አማርኛ"><img src="https://flagcdn.com/w20/et.png" alt=""> አማርኛ — Amharique</button>
            <button class="nuf-sub-item" data-lang="yo"    data-flag="ng" data-name="Yorùbá"><img src="https://flagcdn.com/w20/ng.png" alt=""> Yorùbá</button>
            <button class="nuf-sub-item" data-lang="zu"    data-flag="za" data-name="isiZulu"><img src="https://flagcdn.com/w20/za.png" alt=""> isiZulu — Zoulou</button>
            <button class="nuf-sub-item" data-lang="ta"    data-flag="lk" data-name="தமிழ்"><img src="https://flagcdn.com/w20/lk.png" alt=""> தமிழ் — Tamoul</button>
          </div>
        </li>
      </ul>
    </div>

  </div>

  <div class="nuf-panel-foot">
    <a href="<?= base_url('Home/Contact') ?>" class="nuf-panel-btn primary">
      <i class="bi bi-headset"></i> Nous contacter
    </a>
    <?php if (!$logged_in): ?>
      <a href="<?= base_url('auth') ?>" class="nuf-panel-btn outline">
        <i class="bi bi-box-arrow-in-right"></i> Connexion
      </a>
    <?php else: ?>
      <a href="<?= base_url('auth/logout') ?>" class="nuf-panel-btn outline">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </a>
    <?php endif; ?>
  </div>

</div><!-- end nuf-panel -->

<!-- BOTTOM NAV -->
<div class="nuf-bottom">
  <ul class="nuf-bottom-list">
    <li><a href="<?= base_url() ?>" class="nuf-bottom-link"><i class="bi bi-house-door"></i><span>Accueil</span></a></li>
    <li><a href="<?= base_url('Products') ?>" class="nuf-bottom-link"><i class="bi bi-box-seam"></i><span>Boutique</span></a></li>
    <li><a href="<?= base_url('Medicins') ?>" class="nuf-bottom-link"><i class="bi bi-camera-video"></i><span>Consult.</span></a></li>
    <li><a href="<?= base_url('Home/Contact') ?>" class="nuf-bottom-link"><i class="bi bi-envelope"></i><span>Contact</span></a></li>
    <li>
      <a href="<?= $logged_in ? base_url('home-patient') : base_url('auth') ?>" class="nuf-bottom-link">
        <i class="bi bi-person"></i><span><?= $logged_in ? 'Compte' : 'Connexion' ?></span>
      </a>
    </li>
  </ul>
</div>

<!-- ================================================================
     JAVASCRIPT — UN SEUL BLOC, PROPRE, SANS BUGS
================================================================ -->
<script>
(function () {
  'use strict';

  /* ---------------------------------------------------------------
     UTILITAIRES COOKIES
  --------------------------------------------------------------- */
  function setCookie(name, value, days) {
    var exp = '';
    if (days) {
      var d = new Date();
      d.setTime(d.getTime() + days * 864e5);
      exp = '; expires=' + d.toUTCString();
    }
    document.cookie = name + '=' + (value || '') + exp + '; path=/; SameSite=Lax';
    // Set aussi sur le domaine courant
    var host = window.location.hostname;
    if (host !== 'localhost') {
      document.cookie = name + '=' + (value || '') + exp + '; path=/; domain=.' + host + '; SameSite=Lax';
    }
  }

  function getCookie(name) {
    var v = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    return v ? v.pop() : '';
  }

  function deleteCookie(name) {
    setCookie(name, '', -1);
  }

  /* ---------------------------------------------------------------
     LOADER
  --------------------------------------------------------------- */
  window.addEventListener('load', function () {
    var loader = document.getElementById('nufLoader');
    if (loader) {
      setTimeout(function () {
        loader.classList.add('fade-out');
        setTimeout(function () { loader.style.display = 'none'; }, 450);
      }, 600);
    }
  });

  /* ---------------------------------------------------------------
     HEADER SCROLL
  --------------------------------------------------------------- */
  var header   = document.getElementById('nufHeader');
  var topbar   = document.getElementById('nufTopbar');
  var lastY    = 0;

  window.addEventListener('scroll', function () {
    var y = window.pageYOffset;
    if (y > 80) {
      header.classList.add('scrolled');
      if (topbar) topbar.classList.add('hidden');
      header.classList.toggle('hide-up', y > lastY && y > 200);
    } else {
      header.classList.remove('scrolled', 'hide-up');
      if (topbar) topbar.classList.remove('hidden');
    }
    lastY = y;
  }, { passive: true });

  /* ---------------------------------------------------------------
     MOBILE MENU
  --------------------------------------------------------------- */
  var hamburger = document.getElementById('nufHamburger');
  var panel     = document.getElementById('nufPanel');
  var overlay   = document.getElementById('nufOverlay');
  var panelClose= document.getElementById('nufPanelClose');

  function openPanel() {
    panel.classList.add('open');
    overlay.classList.add('open');
    hamburger.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closePanel() {
    panel.classList.remove('open');
    overlay.classList.remove('open');
    hamburger.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (hamburger)  hamburger.addEventListener('click', openPanel);
  if (panelClose) panelClose.addEventListener('click', closePanel);
  if (overlay)    overlay.addEventListener('click', closePanel);

  /* ---------------------------------------------------------------
     SOUS-MENUS MOBILES
  --------------------------------------------------------------- */
  document.querySelectorAll('.nuf-panel-link[data-sub]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var id  = this.getAttribute('data-sub');
      var sub = document.getElementById('nuf-sub-' + id);
      if (!sub) return;
      var isOpen = sub.classList.contains('open');
      // Fermer tous
      document.querySelectorAll('.nuf-sub').forEach(function (s) { s.classList.remove('open'); s.style.maxHeight = '0'; });
      document.querySelectorAll('.nuf-panel-link[data-sub]').forEach(function (b) { b.classList.remove('open'); });
      // Ouvrir si pas déjà ouvert
      if (!isOpen) {
        sub.classList.add('open');
        sub.style.maxHeight = sub.scrollHeight + 'px';
        this.classList.add('open');
      }
    });
  });

  /* ---------------------------------------------------------------
     RECHERCHE
  --------------------------------------------------------------- */
  var searchToggle  = document.getElementById('nufSearchToggle');
  var searchWrap    = document.getElementById('nufSearch');
  var searchInput   = document.getElementById('nufSearchInput');
  var searchResults = document.getElementById('nufSearchResults');
  var searchTimer;

  if (searchToggle) {
    searchToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      searchWrap.classList.toggle('show');
      if (searchWrap.classList.contains('show')) setTimeout(function () { searchInput.focus(); }, 80);
    });
  }

  document.addEventListener('click', function (e) {
    if (searchWrap && !searchWrap.contains(e.target) && e.target !== searchToggle) {
      searchWrap.classList.remove('show');
      searchResults.classList.remove('open');
    }
  });

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimer);
      var q = this.value.trim();
      if (q.length < 2) { searchResults.classList.remove('open'); return; }
      searchTimer = setTimeout(function () { doSearch(q); }, 280);
    });
  }

  function doSearch(q) {
    searchResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--gray);">Recherche...</div>';
    searchResults.classList.add('open');
    fetch('<?= base_url('search/ajax_search') ?>?q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (data) { renderSearch(data, q); })
      .catch(function () {
        searchResults.innerHTML = '<div style="padding:16px;text-align:center;color:var(--danger);">Erreur de recherche</div>';
      });
  }

  function renderSearch(data, q) {
    var html = '';
    ['produits','actualites','pages'].forEach(function (key) {
      if (data[key] && data[key].length > 0) {
        var labels = { produits:'Produits', actualites:'Actualités', pages:'Pages' };
        var icons  = { produits:'bi-box-seam', actualites:'bi-newspaper', pages:'bi-file-text' };
        html += '<div class="nuf-search-cat">' + labels[key] + '</div>';
        data[key].slice(0, 4).forEach(function (item) {
          html += '<a href="<?= base_url() ?>' + (item.slug || '') + '" class="nuf-search-item">'
            + '<i class="bi ' + icons[key] + '"></i>'
            + '<div><div style="font-weight:600;">' + esc(item.titre) + '</div>'
            + '<div style="font-size:12px;color:var(--gray);">' + esc((item.extrait||'').slice(0,50)) + '</div></div>'
            + '</a>';
        });
      }
    });
    searchResults.innerHTML = html || '<div style="padding:20px;text-align:center;">Aucun résultat pour « ' + esc(q) + ' »</div>';
  }

  function esc(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  /* ---------------------------------------------------------------
     LANGAGE — MOTEUR DE TRADUCTION FIABLE
     ● Cookie googtrans = /fr/{lang}
     ● On ne stocke que le code langue dans localStorage
     ● Retour au français : on efface tout et on recharge
     ● Les boutons "actif" reflètent la langue courante
  --------------------------------------------------------------- */
  var STORAGE_KEY = 'nuf_lang';

  /* Lire la langue active (depuis cookie ou storage) */
  function getActiveLang() {
    var cookie = getCookie('googtrans');      // ex: /fr/en
    if (cookie && cookie !== '/fr/fr') {
      var parts = cookie.split('/');
      return parts[parts.length - 1] || 'fr';
    }
    return localStorage.getItem(STORAGE_KEY) || 'fr';
  }

  /* Appliquer la langue au bouton desktop */
  function refreshLangBtn(lang) {
    var opt = document.querySelector('.nuf-lang-opt[data-lang="' + lang + '"]');
    var flagEl  = document.getElementById('nufLangFlag');
    var labelEl = document.getElementById('nufLangLabel');
    if (opt && flagEl && labelEl) {
      var img = opt.querySelector('img');
      flagEl.src = img ? img.src : 'https://flagcdn.com/w20/fr.png';
      labelEl.textContent = opt.getAttribute('data-name') || lang;
    }
    /* Marquer le bouton actif */
    document.querySelectorAll('.nuf-lang-opt').forEach(function (b) {
      b.classList.toggle('active', b.getAttribute('data-lang') === lang);
    });
  }

  /* Changer de langue — LA seule fonction appelée partout */
  function setLanguage(lang) {
    if (lang === 'fr') {
      /* Retour au français : effacer cookies + storage */
      deleteCookie('googtrans');
      localStorage.removeItem(STORAGE_KEY);
    } else {
      /* Écrire le cookie googtrans attendu par Google Translate */
      deleteCookie('googtrans');
      setCookie('googtrans', '/fr/' + lang, 365);
      localStorage.setItem(STORAGE_KEY, lang);
    }
    /* Recharger la page pour que Google Translate prenne le nouveau cookie */
    window.location.reload();
  }

  /* Init au chargement */
  document.addEventListener('DOMContentLoaded', function () {
    var current = getActiveLang();
    refreshLangBtn(current);

    /* Activer Google Translate sur la bonne langue si pas déjà fait */
    if (current !== 'fr') {
      var tryApply = setInterval(function () {
        var sel = document.querySelector('.goog-te-combo');
        if (sel) {
          clearInterval(tryApply);
          if (sel.value !== current) {
            sel.value = current;
            sel.dispatchEvent(new Event('change'));
          }
        }
      }, 300);
      setTimeout(function () { clearInterval(tryApply); }, 8000);
    }
  });

  /* Boutons langue desktop */
  document.querySelectorAll('.nuf-lang-opt').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var lang = this.getAttribute('data-lang');
      /* Fermer dropdown */
      document.getElementById('nufLangDrop').classList.remove('open');
      document.getElementById('nufLangBtn').classList.remove('open');
      setLanguage(lang);
    });
  });

  /* Boutons langue mobile */
  document.querySelectorAll('#nuf-sub-mob-lang .nuf-sub-item[data-lang]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var lang = this.getAttribute('data-lang');
      closePanel();
      setLanguage(lang);
    });
  });

  /* Toggle dropdown langue desktop */
  var langBtn  = document.getElementById('nufLangBtn');
  var langDrop = document.getElementById('nufLangDrop');
  if (langBtn) {
    langBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      langBtn.classList.toggle('open');
      langDrop.classList.toggle('open');
    });
  }
  document.addEventListener('click', function (e) {
    if (langDrop && !langDrop.contains(e.target) && !langBtn.contains(e.target)) {
      langDrop.classList.remove('open');
      langBtn.classList.remove('open');
    }
  });

  /* Supprimer en continu la barre Google Translate */
  function killGoogleBar() {
    var banner = document.querySelector('.goog-te-banner-frame, iframe.skiptranslate');
    if (banner) { banner.style.cssText = 'display:none!important;height:0!important;'; }
    document.body.style.cssText += 'top:0!important;margin-top:0!important;';
  }
  setInterval(killGoogleBar, 800);
  window.addEventListener('load', function () { setTimeout(killGoogleBar, 1000); });

})(); // IIFE
</script>
