<?php
session_start();
include("../config.php");

$organizer_id = $_SESSION['user_id'] ?? 0;
$my_tournaments = [];
$map_tournaments = [];
$username = $_SESSION['username'] ?? '';

// Fetch tournaments WITH real approval status from DB
$stmt = $conn->prepare("
    SELECT id, name, sport, location AS venue, date, time, format,
           prize, entrance_fee, description, slots_total, slots_taken,
           registration_deadline, is_closed,
           latitude, longitude,
           COALESCE(status, 'pending') AS approval_status
    FROM tournaments
    WHERE created_by = ?
    ORDER BY date ASC
");
if ($stmt) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $my_tournaments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$map_stmt = $conn->prepare("
    SELECT name, sport, date, location AS venue, prize, latitude, longitude
    FROM tournaments
    WHERE created_by = ?
      AND latitude IS NOT NULL
      AND longitude IS NOT NULL
    ORDER BY date ASC
");
if ($map_stmt) {
    $map_stmt->bind_param("s", $username);
    $map_stmt->execute();
    $map_tournaments = $map_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $map_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TournaMeet — Organizer</title>
  <link rel="icon" type="image/png" href="/Tourna/favicon.png">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --orange: #F47B20;
      --orange-dark: #D96210;
      --orange-light: #FFF0E6;
      --orange-mid: #fdb97d;
      --white: #FFFFFF;
      --shadow: 0 2px 16px rgba(244,123,32,0.18);
    }
    body { background: #fafafa; font-family: 'DM Sans', sans-serif; min-height: 100vh; color: #1a1a1a; }

    #transition-ripple {
      position: fixed; border-radius: 50%;
      background: var(--orange);
      width: 40px; height: 40px;
      transform: scale(0); opacity: 0;
      pointer-events: none; z-index: 9998;
      transition: none;
    }
    #transition-ripple.animating {
      transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1),
                  opacity 0.55s cubic-bezier(0.4, 0, 0.2, 1);
      transform: scale(80); opacity: 1;
    }

    .organizer-badge {
      display: inline-flex; align-items: center; gap: 5px;
      background: var(--orange); color: white;
      font-size: 0.65rem; font-weight: 700; letter-spacing: 1.2px;
      text-transform: uppercase; padding: 2px 9px; border-radius: 50px;
      vertical-align: middle; margin-left: 6px;
    }

    nav {
      position: sticky; top: 0; z-index: 200;
      background: var(--white);
      border-bottom: 2px solid var(--orange);
      box-shadow: var(--shadow);
      height: 64px;
      display: grid; grid-template-columns: 1fr 1fr 1fr;
      align-items: center; padding: 0 32px; gap: 12px;
    }
    .nav-left { display: flex; align-items: center; gap: 10px; }
    .logo-icon {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--orange-light); border: 2px solid var(--orange);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .brand { font-family: 'Bebas Neue', sans-serif; font-size: 1.7rem; letter-spacing: 2.5px; color: var(--orange); line-height: 1; }
    .nav-center { display: flex; justify-content: center; }
    .nav-right { display: flex; justify-content: flex-end; align-items: center; gap: 8px; }
    .nav-icon-btn {
      background: var(--orange-light); border: 1.5px solid var(--orange); border-radius: 50%;
      width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;
      cursor: pointer; transition: background 0.2s, transform 0.15s; color: var(--orange);
      text-decoration: none;
    }
    .nav-icon-btn:hover { background: var(--orange); color: var(--white); transform: scale(1.08); }
    .nav-icon-btn:hover svg { stroke: var(--white); }

    main { max-width: 1100px; margin: 0 auto; padding: 40px 32px 80px; }
    .page-header { margin-bottom: 28px; display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px; }
    .page-header-left h1 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: clamp(2rem, 5vw, 3.5rem);
      color: var(--orange); letter-spacing: 3px; line-height: 1; margin-bottom: 4px;
    }
    .page-header-left p { font-size: 0.92rem; color: #888; font-weight: 500; }

    .btn-create {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--orange); color: white; border: none;
      font-family: 'Bebas Neue', sans-serif; font-size: 1.1rem; letter-spacing: 1.5px;
      padding: 10px 22px; border-radius: 50px; cursor: pointer;
      box-shadow: 0 4px 14px rgba(244,123,32,0.35);
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
      text-decoration: none;
    }
    .btn-create:hover { background: var(--orange-dark); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(244,123,32,0.45); }
    .btn-create:active { transform: scale(0.97); }

    .sports-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
    .sport-card {
      background: var(--white); border-radius: 14px; border: 1.5px solid #f0e0d0;
      box-shadow: 0 2px 12px rgba(244,123,32,0.08);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      padding: 28px 20px 22px;
      transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
      text-align: center; position: relative; overflow: hidden; animation: fadeUp 0.4s ease both;
    }
    .sport-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
      background: linear-gradient(90deg, var(--orange), var(--orange-mid));
      opacity: 0; transition: opacity 0.2s; pointer-events: none;
    }
    .sport-card:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(244,123,32,0.18); border-color: var(--orange); }
    .sport-card:hover::before { opacity: 1; }
    .sport-card:hover .icon-wrap { background: var(--orange); }
    .sport-card:hover .icon-wrap svg { stroke: var(--white); }
    .icon-wrap {
      width: 72px; height: 72px; border-radius: 50%;
      background: var(--orange-light); border: 2px solid #f0e0d0;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 12px; transition: background 0.2s; flex-shrink: 0;
    }
    .icon-wrap svg { width: 36px; height: 36px; stroke: var(--orange); transition: stroke 0.2s; }
    .sport-name { font-family: 'Bebas Neue', sans-serif; font-size: 1.15rem; letter-spacing: 1.5px; color: #222; margin-bottom: 4px; }
    .sport-sub  { font-size: 0.75rem; color: #c0a090; font-weight: 500; margin-bottom: 14px; }
    .card-actions { display: flex; gap: 8px; position: relative; z-index: 2; }
    .card-btn {
      flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 5px;
      border: none; border-radius: 8px; font-family: 'DM Sans', sans-serif;
      font-size: 0.75rem; font-weight: 700; padding: 7px 10px; cursor: pointer;
      transition: background 0.18s, transform 0.12s;
    }
    .card-btn-create { background: var(--orange); color: white; }
    .card-btn-create:hover { background: var(--orange-dark); transform: scale(1.03); }
    .card-btn-manage { background: var(--orange-light); color: var(--orange-dark); border: 1.5px solid #f0d0b8; }
    .card-btn-manage:hover { background: #ffe0c8; transform: scale(1.03); }

    .sport-card:nth-child(1) { animation-delay: 0ms; }
    .sport-card:nth-child(2) { animation-delay: 60ms; }
    .sport-card:nth-child(3) { animation-delay: 120ms; }
    .sport-card:nth-child(4) { animation-delay: 180ms; }
    .sport-card:nth-child(5) { animation-delay: 240ms; }
    .sport-card:nth-child(6) { animation-delay: 300ms; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }

    /* ══ MODALS ══ */
    .modal-overlay {
      position: fixed; inset: 0; z-index: 5000;
      display: none; align-items: center; justify-content: center;
      padding: 20px;
      background: rgba(10, 12, 20, 0.72);
      backdrop-filter: blur(6px);
    }
    .modal-overlay.open { display: flex; animation: fadeIn 0.22s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .modal-box {
      background: var(--white); border-radius: 20px; overflow: hidden;
      width: 100%; max-width: 780px; max-height: 92vh; overflow-y: auto;
      box-shadow: 0 32px 80px rgba(0,0,0,0.35);
      animation: slideUp 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes slideUp {
      from { transform: translateY(40px) scale(0.97); opacity: 0; }
      to   { transform: translateY(0) scale(1); opacity: 1; }
    }
    .modal-header {
      background: var(--orange); padding: 18px 24px;
      display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
      position: sticky; top: 0; z-index: 2;
    }
    .modal-title {
      display: flex; align-items: center; gap: 10px;
      font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; letter-spacing: 2px; color: white;
    }
    .modal-close {
      background: rgba(255,255,255,0.18); border: none; color: white;
      width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 15px;
      display: flex; align-items: center; justify-content: center; transition: background 0.2s;
    }
    .modal-close:hover { background: rgba(255,255,255,0.32); }
    .modal-body { padding: 28px 28px 24px; }
    .sport-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 22px; }
    .sport-opt {
      border: 1.5px solid #f0e0d0; border-radius: 10px; padding: 9px 6px;
      text-align: center; cursor: pointer; transition: all 0.18s; background: var(--white);
    }
    .sport-opt:hover { border-color: var(--orange); background: var(--orange-light); }
    .sport-opt.selected { border-color: var(--orange); background: var(--orange); color: white; }
    .sport-opt .opt-name { font-family: 'Bebas Neue', sans-serif; font-size: 0.85rem; letter-spacing: 1px; display: block; margin-top: 3px; }
    .sport-opt .opt-emoji { font-size: 1.3rem; }
    .sport-opt.selected .opt-name { color: white; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 0.8rem; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
    .form-input, .form-select, .form-textarea {
      width: 100%; border: 1.5px solid #e8d8cc; border-radius: 10px;
      padding: 10px 14px; font-family: 'DM Sans', sans-serif; font-size: 0.9rem; color: #333;
      background: #fdfaf8; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
      border-color: var(--orange); box-shadow: 0 0 0 3px rgba(244,123,32,0.15); background: var(--white);
    }
    .form-textarea { resize: vertical; min-height: 80px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-section-label {
      font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: 1.5px;
      color: var(--orange); margin: 20px 0 12px; border-bottom: 1.5px solid var(--orange-light); padding-bottom: 6px;
    }
    .modal-footer { padding: 16px 28px 24px; display: flex; gap: 10px; justify-content: flex-end; }
    .btn-cancel { background: #f5f5f5; color: #666; border: none; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-weight: 600; font-size: 0.9rem; padding: 10px 22px; cursor: pointer; transition: background 0.18s; }
    .btn-cancel:hover { background: #eee; }
    .btn-submit { background: var(--orange); color: white; border: none; border-radius: 10px; font-family: 'Bebas Neue', sans-serif; font-size: 1rem; letter-spacing: 1.5px; padding: 10px 28px; cursor: pointer; transition: background 0.18s, transform 0.12s; box-shadow: 0 4px 12px rgba(244,123,32,0.3); }
    .btn-submit:hover { background: var(--orange-dark); transform: translateY(-1px); }

    /* ══ MANAGE MODAL ══ */
    .manage-modal-box {
      background: var(--white); border-radius: 20px; overflow: hidden;
      width: 100%; max-width: 720px; max-height: 90vh;
      display: flex; flex-direction: column;
      box-shadow: 0 32px 80px rgba(0,0,0,0.35);
      animation: slideUp 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .manage-list { flex: 1; overflow-y: auto; padding: 18px; }
    .t-row-wrap { margin-bottom: 10px; }
    .tournament-row {
      display: flex; align-items: center; gap: 14px;
      background: #fdfaf8; border: 1.5px solid #f0e0d0; border-radius: 12px;
      padding: 14px 16px; cursor: pointer;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .tournament-row:hover { border-color: var(--orange); box-shadow: 0 2px 12px rgba(244,123,32,0.1); }
    .tournament-row:hover .t-name { color: var(--orange); }
    .t-row-wrap.panel-open .tournament-row { border-radius: 12px 12px 0 0; border-bottom-color: transparent; }
    .t-sport-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
    .t-info { flex: 1; min-width: 0; }
    .t-name { font-weight: 700; font-size: 0.92rem; color: #1a1a1a; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color 0.15s; }
    .t-meta { font-size: 0.75rem; color: #999; }

    /* ── Status badges including new approval states ── */
    .t-status { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; padding: 3px 9px; border-radius: 50px; flex-shrink: 0; }
    .t-status.active   { background: #e6f8f0; color: #16a05b; }
    .t-status.upcoming { background: var(--orange-light); color: var(--orange-dark); }
    .t-status.ended    { background: #f5f5f5; color: #999; }
    .t-status.pending-approval { background: #fff8e6; color: #b45309; border: 1px dashed #f59e0b; }
    .t-status.declined-approval { background: #fff0f0; color: #dc2626; }

    .t-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .t-btn { width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.16s; background: var(--white); }
    .t-btn-edit   { border-color: var(--orange); color: var(--orange); }
    .t-btn-edit:hover   { background: var(--orange); color: white; }
    .t-btn-delete { border-color: #ffb3b3; color: #e05252; }
    .t-btn-delete:hover { background: #e05252; color: white; border-color: #e05252; }
    .t-btn-close, .t-btn-reopen { width: auto; padding: 0 12px; font-family: 'DM Sans', sans-serif; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.4px; }
    .t-btn-close  { border-color: #ffd0a0; color: #c07020; }
    .t-btn-close:hover  { background: #F47B20; color: white; border-color: #F47B20; }
    .t-btn-reopen { border-color: #a0e0b8; color: #16a05b; }
    .t-btn-reopen:hover { background: #16a05b; color: white; border-color: #16a05b; }

    /* ── Pending approval banner inside row ── */
    .approval-notice {
      display: flex; align-items: center; gap: 8px;
      margin: 6px 0 0;
      padding: 6px 10px;
      background: #fffbeb; border: 1px dashed #f59e0b; border-radius: 7px;
      font-size: 0.72rem; color: #92400e; font-weight: 600;
    }
    .approval-notice.declined { background: #fff5f5; border-color: #fca5a5; color: #991b1b; }

    /* ── REGISTRANTS PANEL ── */
    .registrants-panel {
      display: none; background: #fdfaf8;
      border: 1.5px solid #f0e0d0; border-top: none;
      border-radius: 0 0 12px 12px;
      padding: 0 16px 14px;
      animation: fadeIn 0.2s ease;
    }
    .registrants-panel.open { display: block; }
    .reg-panel-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 0 10px; border-bottom: 1px solid #f0e0d0; margin-bottom: 10px;
    }
    .reg-panel-title { font-family: 'Bebas Neue', sans-serif; font-size: 0.88rem; letter-spacing: 1.5px; color: var(--orange); }
    .reg-slots-badge { font-size: 0.72rem; font-weight: 700; color: var(--orange-dark); background: var(--orange-light); padding: 3px 10px; border-radius: 50px; border: 1px solid #f0d0b0; }
    .slots-track-sm { height: 5px; background: #f0e0d0; border-radius: 50px; overflow: hidden; margin-bottom: 12px; }
    .slots-fill-sm  { height: 100%; background: linear-gradient(90deg, var(--orange), #fdb97d); border-radius: 50px; transition: width 0.5s ease; }
    .reg-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
    .reg-table th { text-align: left; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #c0a090; padding: 0 8px 6px; }
    .reg-table td { padding: 7px 8px; border-bottom: 1px solid #f8f0e8; color: #444; vertical-align: middle; }
    .reg-table tr:last-child td { border-bottom: none; }
    .reg-table tr:hover td { background: #fff8f4; }
    .reg-status-badge { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; padding: 2px 8px; border-radius: 50px; }
    .reg-status-badge.pending  { background: #fff8e6; color: #c07020; }
    .reg-status-badge.approved { background: #e6f8f0; color: #16a05b; }
    .reg-status-badge.rejected { background: #fff0f0; color: #e05252; }
    .reg-action-btns { display: flex; gap: 4px; }
    .reg-approve-btn { background: #e6f8f0; color: #16a05b; border: 1px solid #a0e0b8; border-radius: 6px; padding: 3px 9px; font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: 'DM Sans', sans-serif; }
    .reg-approve-btn:hover { background: #16a05b; color: white; }
    .reg-reject-btn  { background: #fff0f0; color: #e05252; border: 1px solid #ffb3b3; border-radius: 6px; padding: 3px 9px; font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: 'DM Sans', sans-serif; }
    .reg-reject-btn:hover  { background: #e05252; color: white; }
    .reg-empty   { text-align: center; padding: 20px; color: #ccc; font-size: 0.82rem; }
    .reg-loading { text-align: center; padding: 16px; color: #ccc; font-size: 0.8rem; }
    .manage-empty { text-align: center; padding: 48px 20px; color: #ccc; font-family: 'Bebas Neue', sans-serif; font-size: 1.1rem; letter-spacing: 1px; }
    .manage-empty svg { margin-bottom: 12px; opacity: 0.4; }

    /* ══ MAP MODAL ══ */
    #mapModal {
      position: fixed; inset: 0; z-index: 5000;
      display: none; align-items: center; justify-content: center;
      padding: 20px;
      background: rgba(10, 12, 20, 0.72);
      backdrop-filter: blur(6px);
    }
    #mapModal.open { display: flex; animation: fadeIn 0.22s ease; }
    .map-modal-box { background: var(--white); border-radius: 20px; overflow: hidden; width: 100%; max-width: 880px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 32px 80px rgba(0,0,0,0.35); animation: slideUp 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .map-modal-header { background: var(--orange); padding: 16px 22px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
    .map-modal-title { display: flex; align-items: center; gap: 10px; font-family: 'Bebas Neue', sans-serif; font-size: 1.4rem; letter-spacing: 2px; color: white; }
    .map-modal-close { background: rgba(255,255,255,0.18); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 15px; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
    .map-modal-close:hover { background: rgba(255,255,255,0.32); }
    .map-status-bar { background: var(--orange-light); border-bottom: 1.5px solid #f0e0d0; padding: 10px 22px; display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--orange-dark); font-weight: 600; flex-shrink: 0; }
    .map-status-bar .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--orange); flex-shrink: 0; animation: blink 1.4s infinite; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
    .map-status-bar.located .dot { background: #27AE72; animation: none; }
    #tournamentMap { flex: 1; min-height: 440px; }
    .map-legend { padding: 11px 22px; border-top: 1.5px solid #f0e0d0; display: flex; align-items: center; gap: 18px; flex-wrap: wrap; font-size: 0.78rem; color: #888; flex-shrink: 0; }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
    .tm-popup .leaflet-popup-content-wrapper { border-radius: 12px; border: 2px solid var(--orange); box-shadow: 0 4px 20px rgba(244,123,32,0.22); padding: 0; overflow: hidden; }
    .tm-popup .leaflet-popup-content { margin: 0; }
    .tm-popup .leaflet-popup-tip { background: var(--orange); }
    .popup-inner { padding: 14px 16px; font-family: 'DM Sans', sans-serif; }
    .popup-sport { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--orange); margin-bottom: 4px; }
    .popup-name  { font-weight: 700; font-size: 14px; color: #1a1a1a; margin-bottom: 6px; line-height: 1.3; }
    .popup-row   { display: flex; align-items: center; gap: 5px; font-size: 11.5px; color: #666; margin-bottom: 3px; }
    .popup-prize { margin-top: 8px; padding: 5px 10px; background: var(--orange-light); border-radius: 6px; font-size: 12px; font-weight: 700; color: var(--orange-dark); display: inline-block; }

    /* ── VENUE MAP PICKER ── */
    .venue-map-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 0; }
    .venue-left { display: flex; flex-direction: column; gap: 10px; }
    #venueMap { width: 100%; height: 220px; border-radius: 12px; border: 1.5px solid #e8d8cc; overflow: hidden; transition: border-color 0.2s; position: relative; z-index: 1; }
    #venueMap:hover { border-color: var(--orange); }
    .leaflet-pane, .leaflet-top, .leaflet-bottom { z-index: 1 !important; }
    .modal-box { isolation: isolate; }
    .venue-map-hint { font-size: 0.72rem; color: #bbb; text-align: center; margin-top: 4px; font-style: italic; }
    .venue-search-row { display: flex; gap: 6px; }
    .venue-search-row input { flex: 1; }
    .btn-venue-search { background: var(--orange); color: white; border: none; border-radius: 10px; padding: 0 14px; font-size: 0.8rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: background 0.18s; font-family: 'DM Sans', sans-serif; }
    .btn-venue-search:hover { background: var(--orange-dark); }
    .coords-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

    #toast { position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%) translateY(20px); background: #1a1a1a; color: white; padding: 11px 22px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; opacity: 0; pointer-events: none; z-index: 9999; transition: opacity 0.3s, transform 0.3s; }
    #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    #toast.success { background: #27AE72; }
    #toast.error   { background: #E05252; }

    @media (max-width: 1099px) { .sports-grid { grid-template-columns: repeat(2, 1fr); } nav { padding: 0 20px; } }
    @media (max-width: 599px) { .sports-grid { gap: 14px; } nav { padding: 0 12px; grid-template-columns: auto 1fr auto; } main { padding: 24px 16px 60px; } .icon-wrap { width: 60px; height: 60px; } .icon-wrap svg { width: 28px; height: 28px; } .form-row { grid-template-columns: 1fr; } .sport-selector { grid-template-columns: repeat(2, 1fr); } .page-header { flex-direction: column; align-items: flex-start; } }
  </style>
</head>
<body>

  <div id="transition-ripple"></div>
  <div id="toast"></div>

  <nav>
    <div class="nav-left">
      <div class="logo-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2h12v6a6 6 0 0 1-12 0V2Z"/>
          <path d="M6 4H3a2 2 0 0 0-2 2v1a4 4 0 0 0 4 4h1"/>
          <path d="M18 4h3a2 2 0 0 1 2 2v1a4 4 0 0 1-4 4h-1"/>
          <line x1="12" y1="14" x2="12" y2="18"/>
          <path d="M8 22h8"/><line x1="8" y1="18" x2="16" y2="18"/>
        </svg>
      </div>
      <span class="brand">TournaMeet</span>
      <span class="organizer-badge">Organizer</span>
    </div>
    <div class="nav-center"></div>
    <div class="nav-right">
      <button class="nav-icon-btn" id="mapBtn" title="My Tournaments Map">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </button>
      <a href="organizer_profile.php">
        <button class="nav-icon-btn" id="profileBtn" title="Profile">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </button>
      </a>
    </div>
  </nav>

  <main>
    <div class="page-header">
      <div class="page-header-left">
        <h1>Manage Categories</h1>
        <p>Create and manage tournaments by sport type</p>
      </div>
      <button class="btn-create" onclick="openCreate(null)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Tournament
      </button>
    </div>

    <div class="sports-grid">
      <div class="sport-card" style="animation-delay:0ms">
        <div class="icon-wrap"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="18" r="13"/><line x1="18" y1="5" x2="18" y2="31"/><line x1="5" y1="18" x2="31" y2="18"/><path d="M18 5 C10 8 10 28 18 31" fill="none"/><path d="M18 5 C26 8 26 28 18 31" fill="none"/></svg></div>
        <div class="sport-name">Ball Sports</div>
        <div class="sport-sub">Basketball · Football · Volleyball</div>
        <div class="card-actions">
          <button class="card-btn card-btn-create" onclick="event.stopPropagation(); openCreate('Ball Sports')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Create</button>
          <button class="card-btn card-btn-manage" onclick="event.stopPropagation(); openManage('Ball Sports')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Manage</button>
        </div>
      </div>
      <div class="sport-card" style="animation-delay:60ms">
        <div class="icon-wrap"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="13" cy="12" rx="6" ry="8" transform="rotate(-35 13 12)"/><line x1="7" y1="7" x2="12" y2="19" stroke-width="1" opacity="0.5"/><line x1="10" y1="5" x2="15" y2="17" stroke-width="1" opacity="0.5"/><line x1="13" y1="4" x2="18" y2="16" stroke-width="1" opacity="0.5"/><line x1="7" y1="10" x2="17" y2="6" stroke-width="1" opacity="0.5"/><line x1="7" y1="13" x2="18" y2="9" stroke-width="1" opacity="0.5"/><line x1="8" y1="16" x2="19" y2="12" stroke-width="1" opacity="0.5"/><line x1="17" y1="18" x2="30" y2="32" stroke-width="2.4"/><circle cx="29" cy="10" r="3"/></svg></div>
        <div class="sport-name">Racket Sports</div>
        <div class="sport-sub">Badminton · Tennis · Squash</div>
        <div class="card-actions">
          <button class="card-btn card-btn-create" onclick="event.stopPropagation(); openCreate('Racket Sports')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Create</button>
          <button class="card-btn card-btn-manage" onclick="event.stopPropagation(); openManage('Racket Sports')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Manage</button>
        </div>
      </div>
      <div class="sport-card" style="animation-delay:120ms">
        <div class="icon-wrap"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12 Q3 8 9 8 L15 8 Q18 8 18 12 L18 19 Q18 22 15 22 L9 22 Q3 22 3 18 Z" fill="currentColor" fill-opacity="0.08"/><path d="M3 12 Q3 8 9 8 L15 8 Q18 8 18 12 L18 19 Q18 22 15 22 L9 22 Q3 22 3 18 Z"/><path d="M15 8 Q20 7 19 12 Q18 13 18 12"/><rect x="5" y="22" width="11" height="5" rx="2.5"/><path d="M33 12 Q33 8 27 8 L21 8 Q18 8 18 12 L18 19 Q18 22 21 22 L27 22 Q33 22 33 18 Z" fill="currentColor" fill-opacity="0.08"/><path d="M33 12 Q33 8 27 8 L21 8 Q18 8 18 12 L18 19 Q18 22 21 22 L27 22 Q33 22 33 18 Z"/><path d="M21 8 Q16 7 17 12 Q18 13 18 12"/><rect x="20" y="22" width="11" height="5" rx="2.5"/></svg></div>
        <div class="sport-name">Combatives</div>
        <div class="sport-sub">Boxing · MMA · Karate</div>
        <div class="card-actions">
          <button class="card-btn card-btn-create" onclick="event.stopPropagation(); openCreate('Combatives')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Create</button>
          <button class="card-btn card-btn-manage" onclick="event.stopPropagation(); openManage('Combatives')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Manage</button>
        </div>
      </div>
      <div class="sport-card" style="animation-delay:180ms">
        <div class="icon-wrap"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="6" r="3.5"/><line x1="18" y1="9.5" x2="18" y2="21"/><line x1="18" y1="13" x2="11" y2="16"/><line x1="18" y1="13" x2="25" y2="16"/><line x1="18" y1="21" x2="13" y2="30"/><line x1="18" y1="21" x2="23" y2="30"/></svg></div>
        <div class="sport-name">Endurance</div>
        <div class="sport-sub">Running · Cycling · Triathlon</div>
        <div class="card-actions">
          <button class="card-btn card-btn-create" onclick="event.stopPropagation(); openCreate('Endurance')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Create</button>
          <button class="card-btn card-btn-manage" onclick="event.stopPropagation(); openManage('Endurance')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Manage</button>
        </div>
      </div>
      <div class="sport-card" style="animation-delay:240ms">
        <div class="icon-wrap"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="17" cy="20" r="12"/><circle cx="17" cy="20" r="7.5"/><circle cx="17" cy="20" r="3.5"/><circle cx="17" cy="20" r="1.2" fill="currentColor"/><line x1="32" y1="6" x2="18" y2="20" stroke-width="2.2"/><polyline points="29,6 32,6 32,9" stroke-width="1.8"/></svg></div>
        <div class="sport-name">Precision</div>
        <div class="sport-sub">Archery · Shooting · Darts</div>
        <div class="card-actions">
          <button class="card-btn card-btn-create" onclick="event.stopPropagation(); openCreate('Precision')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Create</button>
          <button class="card-btn card-btn-manage" onclick="event.stopPropagation(); openManage('Precision')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Manage</button>
        </div>
      </div>
      <div class="sport-card" style="animation-delay:300ms">
        <div class="icon-wrap"><svg viewBox="0 0 36 36" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 11 C13 11 9 11 7 14 L6 20 C5 23 5 26 6 29 C7 31 9 33 12 32 C15 31 15 28 17 24 L19 24 C21 28 21 31 24 32 C27 33 29 31 30 29 C31 26 31 23 30 20 L29 14 C27 11 23 11 21 11 Z"/><line x1="11" y1="18" x2="11" y2="23" stroke-width="2"/><line x1="8.5" y1="20.5" x2="13.5" y2="20.5" stroke-width="2"/><circle cx="23" cy="19" r="1.2" fill="currentColor"/><circle cx="26" cy="22" r="1.2" fill="currentColor"/></svg></div>
        <div class="sport-name">E-sports</div>
        <div class="sport-sub">FPS · MOBA · Fighting</div>
        <div class="card-actions">
          <button class="card-btn card-btn-create" onclick="event.stopPropagation(); openCreate('E-sports')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Create</button>
          <button class="card-btn card-btn-manage" onclick="event.stopPropagation(); openManage('E-sports')"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Manage</button>
        </div>
      </div>
    </div>
  </main>

  <!-- CREATE / EDIT MODAL (unchanged) -->
  <div class="modal-overlay" id="createModal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title" id="createModalTitle">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Create Tournament
        </div>
        <button class="modal-close" onclick="closeCreate()">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editTournamentId" value="">
        <div class="form-section-label">Sport Category</div>
        <div class="sport-selector" id="sportSelector">
          <div class="sport-opt" data-sport="Ball Sports" onclick="selectSportOpt(this)"><span class="opt-emoji">⚽</span><span class="opt-name">Ball Sports</span></div>
          <div class="sport-opt" data-sport="Racket Sports" onclick="selectSportOpt(this)"><span class="opt-emoji">🏸</span><span class="opt-name">Racket Sports</span></div>
          <div class="sport-opt" data-sport="Combatives" onclick="selectSportOpt(this)"><span class="opt-emoji">🥊</span><span class="opt-name">Combatives</span></div>
          <div class="sport-opt" data-sport="Endurance" onclick="selectSportOpt(this)"><span class="opt-emoji">🏃</span><span class="opt-name">Endurance</span></div>
          <div class="sport-opt" data-sport="Precision" onclick="selectSportOpt(this)"><span class="opt-emoji">🎯</span><span class="opt-name">Precision</span></div>
          <div class="sport-opt" data-sport="E-sports" onclick="selectSportOpt(this)"><span class="opt-emoji">🎮</span><span class="opt-name">E-sports</span></div>
        </div>
        <div class="form-section-label">Tournament Details</div>
        <div class="form-group"><label class="form-label">Tournament Name *</label><input type="text" class="form-input" id="f-name" placeholder="e.g. Baguio Open Basketball 2026"></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Date *</label><input type="date" class="form-input" id="f-date"></div>
          <div class="form-group"><label class="form-label">Time</label><input type="time" class="form-input" id="f-time"></div>
        </div>
        <div class="venue-map-wrap">
          <div class="venue-left">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Venue / Location *</label>
              <div class="venue-search-row">
                <input type="text" class="form-input" id="f-venue" placeholder="e.g. Baguio City Sports Complex">
                <button type="button" class="btn-venue-search" onclick="searchVenue()">Search</button>
              </div>
            </div>
            <div class="coords-row">
              <div class="form-group" style="margin-bottom:0"><label class="form-label">Latitude</label><input type="number" step="any" class="form-input" id="f-lat" placeholder="16.4126" oninput="updatePinFromCoords()"></div>
              <div class="form-group" style="margin-bottom:0"><label class="form-label">Longitude</label><input type="number" step="any" class="form-input" id="f-lng" placeholder="120.5960" oninput="updatePinFromCoords()"></div>
            </div>
          </div>
          <div>
            <label class="form-label">Pin on Map</label>
            <div id="venueMap"></div>
            <div class="venue-map-hint">Click map to set location</div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Prize / Reward</label><input type="text" class="form-input" id="f-prize" placeholder="e.g. ₱50,000 Cash Prize"></div>
          <div class="form-group"><label class="form-label">Entrance Fee (₱)</label><input type="number" min="0" step="0.01" class="form-input" id="f-fee" placeholder="e.g. 150 (leave blank if free)"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Max Participants</label><input type="number" class="form-input" id="f-max" placeholder="e.g. 32"></div>
          <div class="form-group">
            <label class="form-label">Format</label>
            <select class="form-select" id="f-format">
              <option value="">Select format…</option>
              <option value="Single Elimination">Single Elimination</option>
              <option value="Double Elimination">Double Elimination</option>
              <option value="Round Robin">Round Robin</option>
              <option value="Swiss">Swiss</option>
              <option value="League">League</option>
              <option value="Tryouts">Tryouts</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Registration Deadline</label><input type="date" class="form-input" id="f-deadline"></div>
        <div class="form-group"><label class="form-label">Description</label><textarea class="form-textarea" id="f-desc" placeholder="Describe the tournament rules, eligibility, or any other details…"></textarea></div>
      </div>
      <div class="modal-footer">
        <button class="btn-cancel" onclick="closeCreate()">Cancel</button>
        <button class="btn-submit" id="submitBtn" onclick="submitTournament()">Create Tournament</button>
      </div>
    </div>
  </div>

  <!-- MANAGE MODAL -->
  <div class="modal-overlay" id="manageModal">
    <div class="manage-modal-box modal-box" style="display:flex;flex-direction:column;">
      <div class="modal-header">
        <div class="modal-title" id="manageModalTitle">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Manage Tournaments
        </div>
        <button class="modal-close" onclick="closeManage()">✕</button>
      </div>
      <div class="manage-list" id="manageList"></div>
    </div>
  </div>

  <!-- MAP MODAL -->
  <div id="mapModal">
    <div class="map-modal-box">
      <div class="map-modal-header">
        <div class="map-modal-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          My Tournament Locations
        </div>
        <button class="map-modal-close" id="mapClose">✕</button>
      </div>
      <div class="map-status-bar" id="mapStatusBar"><div class="dot"></div><span id="mapStatusText">Requesting your location…</span></div>
      <div id="tournamentMap"></div>
      <div class="map-legend">
        <span style="font-weight:600;color:#555;margin-right:2px;">Legend:</span>
        <div class="legend-item"><div class="legend-dot" style="background:#F47B20;"></div>Ball Sports</div>
        <div class="legend-item"><div class="legend-dot" style="background:#8B5CF6;"></div>Racket</div>
        <div class="legend-item"><div class="legend-dot" style="background:#EF4444;"></div>Combatives</div>
        <div class="legend-item"><div class="legend-dot" style="background:#10B981;"></div>Endurance</div>
        <div class="legend-item"><div class="legend-dot" style="background:#F59E0B;"></div>Precision</div>
        <div class="legend-item"><div class="legend-dot" style="background:#3B82F6;"></div>E-Sports</div>
        <div class="legend-item"><div class="legend-dot" style="background:#1877F2;border:2px solid white;box-shadow:0 0 0 2px #1877F2;"></div>You</div>
        <div class="legend-item" style="margin-left:auto;font-size:0.7rem;color:#ccc;">© OpenStreetMap</div>
      </div>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
  let MY_TOURNAMENTS = <?php echo json_encode(array_map(function($t) {
    $approvalStatus = $t['approval_status'] ?? 'pending';
    // Determine display status based on both approval and tournament state
    $status = 'upcoming';
    if (!empty($t['is_closed']) && $t['is_closed'] == 1) {
        $status = 'ended';
    } elseif (!empty($t['date']) && strtotime($t['date']) < strtotime('today')) {
        $status = 'ended';
    } elseif (!empty($t['registration_deadline']) && strtotime($t['registration_deadline']) >= strtotime('now')) {
        $status = 'active';
    }
    return [
      'id'             => $t['id'],
      'name'           => $t['name'],
      'sport'          => $t['sport'],
      'date'           => $t['date'],
      'time'           => $t['time'] ?? '',
      'venue'          => $t['venue'] ?? '',
      'prize'          => $t['prize'] ?? '',
      'fee'            => (float)($t['entrance_fee'] ?? 0),
      'status'         => $status,
      'approval_status'=> $approvalStatus,
      'lat'            => (float)($t['latitude'] ?? 0),
      'lng'            => (float)($t['longitude'] ?? 0),
      'slots'          => (int)($t['slots_total'] ?? 0),
      'taken'          => (int)($t['slots_taken'] ?? 0),
      'format'         => $t['format'] ?? '',
      'deadline'       => $t['registration_deadline'] ?? '',
      'is_closed'      => (int)($t['is_closed'] ?? 0),
    ];
  }, $my_tournaments)); ?>;

  const MAP_TOURNAMENTS = <?php echo json_encode(array_map(function($t) {
    return [
      'name'  => $t['name'],
      'sport' => $t['sport'],
      'lat'   => (float)$t['latitude'],
      'lng'   => (float)$t['longitude'],
      'date'  => date('M j, Y', strtotime($t['date'])),
      'prize' => $t['prize'],
      'venue' => $t['venue'],
    ];
  }, $map_tournaments)); ?>;

  const SPORT_COLORS = {
    'Ball Sports':   '#F47B20',
    'Racket Sports': '#8B5CF6',
    'Combatives':    '#EF4444',
    'Endurance':     '#10B981',
    'Precision':     '#F59E0B',
    'E-sports':      '#3B82F6',
  };

  function showToast(msg, type = '') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = 'show ' + type;
    setTimeout(() => t.className = '', 2800);
  }

  function esc(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  const profileBtn = document.getElementById('profileBtn');
  const ripple     = document.getElementById('transition-ripple');
  profileBtn.addEventListener('click', function () {
    const rect = profileBtn.getBoundingClientRect();
    ripple.style.left = (rect.left + rect.width/2 - 20) + 'px';
    ripple.style.top  = (rect.top + rect.height/2 - 20) + 'px';
    requestAnimationFrame(() => ripple.classList.add('animating'));
    setTimeout(() => { window.location.href = 'organizer_profile.php'; }, 500);
  });

  /* ════ CREATE / EDIT MODAL ════ */
  let editingId = null;

  function openCreate(sport) {
    editingId = null;
    document.getElementById('editTournamentId').value = '';
    document.getElementById('createModalTitle').innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create Tournament`;
    document.getElementById('submitBtn').textContent = 'Create Tournament';
    clearForm();
    if (sport) preselectSport(sport);
    document.getElementById('createModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function openEdit(id) {
    const t = MY_TOURNAMENTS.find(x => x.id == id);
    if (!t) return;
    editingId = id;
    document.getElementById('editTournamentId').value = id;
    document.getElementById('createModalTitle').innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit Tournament`;
    document.getElementById('submitBtn').textContent = 'Save Changes';
    preselectSport(t.sport);
    document.getElementById('f-name').value  = t.name;
    document.getElementById('f-date').value  = t.date;
    document.getElementById('f-time').value  = t.time;
    document.getElementById('f-venue').value = t.venue;
    document.getElementById('f-prize').value = t.prize;
    document.getElementById('f-fee').value   = t.fee || '';
    document.getElementById('f-lat').value   = t.lat || '';
    document.getElementById('f-lng').value   = t.lng || '';
    document.getElementById('createModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeCreate() {
    document.getElementById('createModal').classList.remove('open');
    document.body.style.overflow = '';
  }

  function clearForm() {
    ['f-name','f-date','f-time','f-venue','f-prize','f-fee','f-lat','f-lng','f-max','f-desc','f-deadline'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-format').value = '';
    document.querySelectorAll('.sport-opt').forEach(o => o.classList.remove('selected'));
  }

  function preselectSport(sport) {
    document.querySelectorAll('.sport-opt').forEach(o => o.classList.toggle('selected', o.dataset.sport === sport));
  }

  function selectSportOpt(el) {
    document.querySelectorAll('.sport-opt').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
  }

  function submitTournament() {
    const sport = document.querySelector('.sport-opt.selected')?.dataset.sport;
    const name  = document.getElementById('f-name').value.trim();
    const date  = document.getElementById('f-date').value;
    const venue = document.getElementById('f-venue').value.trim();
    if (!sport) { showToast('Please select a sport category', 'error'); return; }
    if (!name)  { showToast('Tournament name is required', 'error'); return; }
    if (!date)  { showToast('Date is required', 'error'); return; }
    if (!venue) { showToast('Venue is required', 'error'); return; }
    const payload = {
      id: editingId, sport, name, date,
      time:     document.getElementById('f-time').value,
      venue,
      prize:    document.getElementById('f-prize').value,
      fee:      document.getElementById('f-fee').value,
      lat:      document.getElementById('f-lat').value,
      lng:      document.getElementById('f-lng').value,
      max:      document.getElementById('f-max').value,
      format:   document.getElementById('f-format').value,
      deadline: document.getElementById('f-deadline').value,
      desc:     document.getElementById('f-desc').value
    };
    const url    = editingId ? '/Tourna/Organizer/api/update_tournament.php' : '/Tourna/Organizer/api/create_tournament.php';
    const method = editingId ? 'PUT' : 'POST';
    fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        if (editingId) {
          const idx = MY_TOURNAMENTS.findIndex(x => x.id == editingId);
          if (idx > -1) MY_TOURNAMENTS[idx] = { ...MY_TOURNAMENTS[idx], ...payload, id: editingId };
          showToast('Tournament updated!', 'success');
        } else {
          MY_TOURNAMENTS.push({ ...payload, id: data.id, slots: parseInt(payload.max)||0, taken: 0, is_closed: 0, status: 'upcoming', approval_status: 'pending' });
          showToast('Tournament created! Waiting for admin approval.', 'success');
        }
        closeCreate();
        if (document.getElementById('manageModal').classList.contains('open') && currentManageSport) renderManageList(currentManageSport);
      } else { showToast(data.message || 'Something went wrong', 'error'); }
    })
    .catch(() => showToast('Network error. Please try again.', 'error'));
  }

  /* ════ MANAGE MODAL ════ */
  let currentManageSport = null;
  let openPanelId = null;

  function openManage(sport) {
    currentManageSport = sport;
    openPanelId = null;
    document.getElementById('manageModalTitle').innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Manage — ${sport}`;
    renderManageList(sport);
    document.getElementById('manageModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function renderManageList(sport) {
    const list  = document.getElementById('manageList');
    const items = MY_TOURNAMENTS.filter(t => t.sport === sport);
    if (!items.length) {
      list.innerHTML = `<div class="manage-empty"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" style="display:block;margin:0 auto 10px"><path d="M6 2h12v6a6 6 0 0 1-12 0V2Z"/></svg>No ${sport} tournaments yet.<br><span style="font-family:'DM Sans',sans-serif;font-size:0.8rem;font-weight:500;color:#bbb;">Click Create to add one!</span></div>`;
      return;
    }
    list.innerHTML = items.map(t => {
      const color      = SPORT_COLORS[t.sport] || '#ccc';
      const isClosed   = t.is_closed == 1;
      const approval   = t.approval_status || 'pending';

      // ── Status badge: approval takes priority for pending/declined ──
      let stCls, stLabel;
      if (approval === 'pending') {
        stCls = 'pending-approval'; stLabel = '⏳ Pending Approval';
      } else if (approval === 'declined') {
        stCls = 'declined-approval'; stLabel = '✕ Declined';
      } else {
        // approved — show normal tournament status
        stCls  = isClosed ? 'ended' : (t.status === 'active' ? 'active' : t.status === 'ended' ? 'ended' : 'upcoming');
        stLabel = isClosed ? 'Closed' : (t.status === 'active' ? 'Active' : t.status === 'ended' ? 'Ended' : 'Upcoming');
      }

      const dateStr = t.date ? new Date(t.date).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }) : '';
      const taken   = t.taken || 0;
      const total   = t.slots || 0;
      const feeStr  = t.fee > 0 ? ` · ₱${parseFloat(t.fee).toLocaleString()} fee` : ' · Free';

      // ── Notice banner under the row for pending/declined ──
      const noticeBanner = approval === 'pending'
        ? `<div class="approval-notice">⏳ Awaiting admin approval — not yet visible to athletes</div>`
        : approval === 'declined'
        ? `<div class="approval-notice declined">✕ Declined by admin — not visible to athletes</div>`
        : '';

      // ── Only show close/reopen if approved ──
      const closeReopenBtn = approval === 'approved'
        ? (isClosed
            ? `<button class="t-btn t-btn-reopen" onclick="toggleRegistration(${t.id}, 0)">Reopen</button>`
            : `<button class="t-btn t-btn-close"  onclick="toggleRegistration(${t.id}, 1)">Close</button>`)
        : '';

      return `
      <div class="t-row-wrap" id="trow-${t.id}">
        <div class="tournament-row" onclick="toggleRegistrants(${t.id}, ${total})">
          <div class="t-sport-dot" style="background:${color}"></div>
          <div class="t-info">
            <div class="t-name">${esc(t.name)}</div>
            <div class="t-meta">${dateStr}${t.venue ? ' · ' + esc(t.venue) : ''} · 👥 ${taken}${total ? '/' + total : ''}${feeStr}</div>
            ${noticeBanner}
          </div>
          <span class="t-status ${stCls}">${stLabel}</span>
          <div class="t-actions" onclick="event.stopPropagation()">
            ${closeReopenBtn}
            <button class="t-btn t-btn-edit" title="Edit" onclick="editFromManage(${t.id})">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button class="t-btn t-btn-delete" title="Delete" onclick="deleteTournament(${t.id})">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
          </div>
        </div>
        <div class="registrants-panel" id="rpanel-${t.id}"></div>
      </div>`;
    }).join('');
  }

  /* ════ REGISTRANTS PANEL ════ */
  function toggleRegistrants(id, totalSlots) {
    const panel = document.getElementById('rpanel-' + id);
    const wrap  = document.getElementById('trow-' + id);
    if (!panel) return;
    if (panel.classList.contains('open')) {
      panel.classList.remove('open'); wrap.classList.remove('panel-open'); openPanelId = null; return;
    }
    if (openPanelId && openPanelId !== id) {
      const prev = document.getElementById('rpanel-' + openPanelId);
      const prevWrap = document.getElementById('trow-' + openPanelId);
      if (prev) prev.classList.remove('open');
      if (prevWrap) prevWrap.classList.remove('panel-open');
    }
    openPanelId = id;
    panel.classList.add('open'); wrap.classList.add('panel-open');
    panel.innerHTML = '<div class="reg-loading">⏳ Loading registrants…</div>';
    fetch('/Tourna/Organizer/api/get_registrants.php?tournament_id=' + id)
      .then(r => r.json())
      .then(data => {
        if (!data.success) { panel.innerHTML = '<div class="reg-empty">Could not load registrants.</div>'; return; }
        const regs  = data.registrants || [];
        const taken = regs.length;
        const pct   = totalSlots ? Math.min(100, Math.round((taken / totalSlots) * 100)) : 0;
        const t = MY_TOURNAMENTS.find(x => x.id == id);
        if (t) t.taken = taken;
        const metaEl = document.querySelector('#trow-' + id + ' .t-meta');
        if (metaEl) {
          const dateStr = t && t.date ? new Date(t.date).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }) : '';
          const feeStr  = t && t.fee > 0 ? ` · ₱${parseFloat(t.fee).toLocaleString()} fee` : ' · Free';
          metaEl.textContent = dateStr + (t && t.venue ? ' · ' + t.venue : '') + ' · 👥 ' + taken + (totalSlots ? '/' + totalSlots : '') + feeStr;
        }
        const slotsHtml = `<div class="reg-panel-header"><span class="reg-panel-title">👥 Registrants</span><span class="reg-slots-badge">${taken}${totalSlots ? ' / ' + totalSlots : ''} slots taken</span></div><div class="slots-track-sm"><div class="slots-fill-sm" style="width:${pct}%"></div></div>`;
        if (!regs.length) { panel.innerHTML = slotsHtml + '<div class="reg-empty">No one has registered yet.</div>'; return; }
        const rows = regs.map(r => `
          <tr id="regrow-${r.id}">
            <td><strong>${esc(r.athlete_username)}</strong></td>
            <td>${esc(r.team_name || '—')}</td>
            <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(r.members || '—')}</td>
            <td>${r.joined_at ? new Date(r.joined_at).toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'}) : '—'}</td>
            <td>${r.status === 'pending' ? `<div class="reg-action-btns"><button class="reg-approve-btn" onclick="updateRegStatus(${r.id},'approved',${id},${totalSlots})">Approve</button><button class="reg-reject-btn" onclick="updateRegStatus(${r.id},'rejected',${id},${totalSlots})">Reject</button></div>` : `<span class="reg-status-badge ${r.status}">${r.status}</span>`}</td>
          </tr>`).join('');
        panel.innerHTML = slotsHtml + `<table class="reg-table"><thead><tr><th>Username</th><th>Team</th><th>Members</th><th>Joined</th><th>Status</th></tr></thead><tbody>${rows}</tbody></table>`;
      })
      .catch(() => { panel.innerHTML = '<div class="reg-empty">Network error.</div>'; });
  }

  function updateRegStatus(regId, status, tournamentId, totalSlots) {
    fetch('/Tourna/Organizer/api/update_reg_status.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: regId, status }) })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(status === 'approved' ? '✅ Registration approved' : '❌ Registration rejected', 'success');
        const panel = document.getElementById('rpanel-' + tournamentId);
        const wrap  = document.getElementById('trow-' + tournamentId);
        if (panel) { panel.classList.remove('open'); if (wrap) wrap.classList.remove('panel-open'); openPanelId = null; }
        setTimeout(() => toggleRegistrants(tournamentId, totalSlots), 80);
      } else { showToast(data.message || 'Failed to update', 'error'); }
    })
    .catch(() => showToast('Network error', 'error'));
  }

  function editFromManage(id) { closeManage(); setTimeout(() => openEdit(id), 120); }

  function deleteTournament(id) {
    if (!confirm('Delete this tournament? This cannot be undone.')) return;
    fetch('/Tourna/Organizer/api/delete_tournament.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        MY_TOURNAMENTS = MY_TOURNAMENTS.filter(x => x.id != id);
        const row = document.getElementById('trow-' + id);
        if (row) { row.style.opacity = '0'; row.style.transform = 'translateX(20px)'; row.style.transition = 'all 0.25s'; setTimeout(() => { row.remove(); if (currentManageSport) renderManageList(currentManageSport); }, 260); }
        showToast('Tournament deleted', 'success');
      } else { showToast(data.message || 'Delete failed', 'error'); }
    })
    .catch(() => showToast('Network error', 'error'));
  }

  function toggleRegistration(id, closedVal) {
    if (!confirm(closedVal ? 'Close registration for this tournament?' : 'Reopen registration for this tournament?')) return;
    fetch('/Tourna/Organizer/api/toggle_registration.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, is_closed: closedVal }) })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const t = MY_TOURNAMENTS.find(x => x.id == id);
        if (t) t.is_closed = closedVal;
        if (currentManageSport) renderManageList(currentManageSport);
        showToast(closedVal ? 'Registration closed' : 'Registration reopened', 'success');
      } else { showToast(data.message || 'Failed', 'error'); }
    })
    .catch(() => showToast('Network error', 'error'));
  }

  function closeManage() {
    document.getElementById('manageModal').classList.remove('open');
    document.body.style.overflow = '';
    openPanelId = null;
  }

  /* ════ MAP MODAL ════ */
  let leafletMap = null, mapInitiated = false;
  function pinIcon(color) { return L.divIcon({ className: '', html: `<div style="width:34px;height:34px;border-radius:50% 50% 50% 0;background:${color};border:3px solid #fff;box-shadow:0 3px 12px rgba(0,0,0,0.28);transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);font-size:13px;line-height:1;">🏆</span></div>`, iconSize:[34,34], iconAnchor:[17,34], popupAnchor:[0,-36] }); }
  function youIcon() { return L.divIcon({ className: '', html: `<div style="width:18px;height:18px;border-radius:50%;background:#1877F2;border:3px solid #fff;box-shadow:0 0 0 4px rgba(24,119,242,0.28);"></div>`, iconSize:[18,18], iconAnchor:[9,9] }); }

  function buildMap(lat, lng) {
    if (leafletMap) { leafletMap.setView([lat, lng], 13); leafletMap.invalidateSize(); return; }
    leafletMap = L.map('tournamentMap', { center: [lat, lng], zoom: 13 });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom: 19 }).addTo(leafletMap);
    L.marker([lat, lng], { icon: youIcon() }).addTo(leafletMap).bindPopup('<div style="font-family:DM Sans,sans-serif;font-size:13px;font-weight:700;padding:6px 10px;">📍 You are here</div>');
    MAP_TOURNAMENTS.forEach(t => {
      const color = SPORT_COLORS[t.sport] || '#F47B20';
      L.marker([t.lat, t.lng], { icon: pinIcon(color) }).addTo(leafletMap).bindPopup(`<div class="popup-inner"><div class="popup-sport">${t.sport}</div><div class="popup-name">${t.name}</div><div class="popup-row">${t.date}</div><div class="popup-row">${t.venue}</div><div class="popup-prize">🏆 ${t.prize}</div></div>`, { className: 'tm-popup', maxWidth: 230 });
    });
    const pts = [[lat, lng], ...MAP_TOURNAMENTS.map(t => [t.lat, t.lng])].filter(p => p[0] && p[1]);
    if (pts.length > 1) leafletMap.fitBounds(pts, { padding: [40, 40] });
  }

  const mapModal = document.getElementById('mapModal');
  const mapBtn   = document.getElementById('mapBtn');
  const mapClose = document.getElementById('mapClose');
  const statusBar  = document.getElementById('mapStatusBar');
  const statusText = document.getElementById('mapStatusText');

  function openMap() {
    mapModal.classList.add('open'); document.body.style.overflow = 'hidden';
    if (mapInitiated) { setTimeout(() => leafletMap && leafletMap.invalidateSize(), 120); return; }
    mapInitiated = true;
    setTimeout(() => {
      if (!navigator.geolocation) { statusText.textContent = 'Geolocation unavailable — showing Baguio area'; statusBar.classList.add('located'); buildMap(16.4126, 120.5960); return; }
      navigator.geolocation.getCurrentPosition(
        pos => { statusText.textContent = 'Located — showing your tournament locations'; statusBar.classList.add('located'); buildMap(pos.coords.latitude, pos.coords.longitude); setTimeout(() => leafletMap && leafletMap.invalidateSize(), 120); },
        ()  => { statusText.textContent = 'Location denied — showing Baguio area'; statusBar.classList.add('located'); buildMap(16.4126, 120.5960); setTimeout(() => leafletMap && leafletMap.invalidateSize(), 120); },
        { timeout: 8000, maximumAge: 60000 }
      );
    }, 80);
  }

  /* ════ VENUE MAP PICKER ════ */
  let venueMap = null, venueMarker = null;
  function initVenueMap() {
    if (venueMap) { venueMap.invalidateSize(); return; }
    const defaultLat = parseFloat(document.getElementById('f-lat').value) || 16.4126;
    const defaultLng = parseFloat(document.getElementById('f-lng').value) || 120.5960;
    venueMap = L.map('venueMap', { center: [defaultLat, defaultLng], zoom: 13, zoomControl: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(venueMap);
    if (document.getElementById('f-lat').value && document.getElementById('f-lng').value) placeVenuePin(defaultLat, defaultLng);
    venueMap.on('click', function(e) { placeVenuePin(e.latlng.lat, e.latlng.lng); reverseGeocode(e.latlng.lat, e.latlng.lng); });
  }
  function placeVenuePin(lat, lng) {
    if (venueMarker) venueMap.removeLayer(venueMarker);
    venueMarker = L.marker([lat, lng], { icon: L.divIcon({ className: '', html: `<div style="width:30px;height:30px;border-radius:50% 50% 50% 0;background:var(--orange);border:3px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,0.3);transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);font-size:12px;">📍</span></div>`, iconSize:[30,30], iconAnchor:[15,30], popupAnchor:[0,-32] }), draggable: true }).addTo(venueMap);
    document.getElementById('f-lat').value = lat.toFixed(7);
    document.getElementById('f-lng').value = lng.toFixed(7);
    venueMarker.on('dragend', function(e) { const pos = e.target.getLatLng(); document.getElementById('f-lat').value = pos.lat.toFixed(7); document.getElementById('f-lng').value = pos.lng.toFixed(7); reverseGeocode(pos.lat, pos.lng); });
  }
  function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`).then(r => r.json()).then(data => { if (data && data.display_name) { const v = document.getElementById('f-venue'); if (!v.value) v.value = data.display_name.split(',').slice(0,3).join(',').trim(); } }).catch(() => {});
  }
  function searchVenue() {
    const q = document.getElementById('f-venue').value.trim();
    if (!q) { showToast('Enter a venue name to search', 'error'); return; }
    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1`).then(r => r.json()).then(results => { if (!results.length) { showToast('Location not found', 'error'); return; } const r = results[0]; const lat = parseFloat(r.lat), lng = parseFloat(r.lon); if (!venueMap) initVenueMap(); venueMap.setView([lat, lng], 15); placeVenuePin(lat, lng); }).catch(() => showToast('Search failed', 'error'));
  }
  function updatePinFromCoords() {
    const lat = parseFloat(document.getElementById('f-lat').value);
    const lng = parseFloat(document.getElementById('f-lng').value);
    if (isNaN(lat) || isNaN(lng)) return;
    if (!venueMap) initVenueMap();
    venueMap.setView([lat, lng], 15); placeVenuePin(lat, lng);
  }

  const _origOpenCreate = openCreate;
  openCreate = function(sport) { _origOpenCreate(sport); setTimeout(() => initVenueMap(), 180); };
  const _origOpenEdit = openEdit;
  openEdit = function(id) {
    _origOpenEdit(id);
    setTimeout(() => {
      if (venueMap) venueMap.invalidateSize(); else initVenueMap();
      const lat = parseFloat(document.getElementById('f-lat').value);
      const lng = parseFloat(document.getElementById('f-lng').value);
      if (!isNaN(lat) && !isNaN(lng) && lat !== 0) { venueMap.setView([lat, lng], 15); placeVenuePin(lat, lng); }
    }, 180);
  };
  const _origCloseCreate = closeCreate;
  closeCreate = function() { _origCloseCreate(); if (venueMap) { venueMap.remove(); venueMap = null; venueMarker = null; } };

  mapBtn.addEventListener('click', openMap);
  mapClose.addEventListener('click', () => { mapModal.classList.remove('open'); document.body.style.overflow = ''; });
  mapModal.addEventListener('click', e => { if (e.target === mapModal) { mapModal.classList.remove('open'); document.body.style.overflow = ''; } });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeCreate(); closeManage(); mapModal.classList.remove('open'); document.body.style.overflow = ''; } });
  </script>
</body>
</html>