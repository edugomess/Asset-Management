<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
<link rel="stylesheet" href="/assets/css/responsive_fix.css">
<style>
    /* NProgress Premium Colorization */
    #nprogress .bar { background: #1cc88a !important; height: 3px !important; }
    #nprogress .peg { box-shadow: 0 0 10px #1cc88a, 0 0 5px #1cc88a !important; }
    #nprogress .spinner-icon { border-top-color: #1cc88a !important; border-left-color: #1cc88a !important; }

    /* GLOBAL TYPOGRAPHY & PREMIUM BALANCED DESIGN */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        font-size: 0.9rem !important;
        color: #1e293b !important;
        line-height: 1.6 !important;
        background-color: #f8fafc;
        -webkit-font-smoothing: antialiased;
    }

    /* PREMIUM SIDEBAR (DESKTOP) */
    @media (min-width: 768px) {
        .sidebar {
            width: 240px !important;
            min-height: 100vh;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
        }
        #content-wrapper {
            margin-left: 240px !important;
        }
    }
    /* Premium Sidebar Design - Glassmorphism & Animations */
    :root {
        --sidebar-bg-start: #06222b;
        --sidebar-bg-end: #05323f;
        --sidebar-accent: #103742ef;
        --sidebar-accent-glow: rgb(0, 0, 0);
        --sidebar-hover: rgba(255, 255, 255, 0.1);
        --sidebar-text: #edeff3;
        --sidebar-text-active: #ffffff;

        /* Light Mode Defaults */
        --bg-color: #f8f9fc;
        --card-bg: #fff;
        --text-color: #5a5c69;
        --heading-color: #4e73df;
        --border-color: #e3e6f0;
        --topbar-bg: #fff;
    }

    /* BASE SIDEBAR STRUCTURE */
    nav.sidebar {
        position: fixed !important;
        top: 0;
        left: 0;
        bottom: 0;
        width: 15rem !important;
        background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-end) 100%) !important;
        box-shadow: 10px 0 30px rgba(0, 0, 0, 0.2) !important;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 1060;
        display: flex !important;
        flex-direction: column !important;
        min-height: 100vh !important;
    }

    /* DESKTOP BEHAVIOR (>= 768px) */
    @media (min-width: 768px) {
        nav.sidebar {
            transform: translateX(0) !important;
        }
        nav.sidebar.toggled {
            width: 6.5rem !important;
        }
        #content-wrapper {
            margin-left: 15rem;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.toggled + #content-wrapper {
            margin-left: 6.5rem;
        }
        /* Hide labels in slim mode */
        .sidebar.toggled .nav-link span,
        .sidebar.toggled .sidebar-heading,
        .sidebar.toggled .sidebar-brand-text {
            display: none !important;
        }
        .sidebar.toggled .nav-link i {
            margin-right: 0 !important;
            font-size: 1.4rem;
        }
        .sidebar.toggled .nav-link {
            justify-content: center !important;
        }
    }

    /* MOBILE BEHAVIOR (< 768px) */
    @media (max-width: 768px) {
        nav.sidebar {
            transform: translateX(-100%) !important;
            width: 15rem !important;
        }
        nav.sidebar.toggled {
            transform: translateX(0) !important;
        }
        #content-wrapper {
            margin-left: 0 !important;
            width: 100% !important;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1055;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        body.sidebar-toggled .sidebar-overlay {
            display: block;
            opacity: 1;
        }
        /* Always show labels on mobile when open */
        .sidebar.toggled .nav-link span,
        .sidebar.toggled .sidebar-heading,
        .sidebar.toggled .sidebar-brand-text {
            display: inline-block !important;
        }
    }

    /* INNER LIST SCROLLING */
    ul.sidebar {
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        margin: 0 !important;
        padding-top: 1rem;
        padding-bottom: 5rem;
        background: transparent !important;
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.1) transparent;
    }

    ul.sidebar::-webkit-scrollbar { width: 4px; }
    ul.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* BRAND HEADER */
    .sidebar-brand {
        height: 5rem !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        background: var(--sidebar-bg-start);
        text-decoration: none !important;
        z-index: 1061;
    }

    /* NAV ITEMS & LINKS */
    .nav-item { margin: 0.2rem 1rem !important; }
    .nav-link {
        color: var(--sidebar-text) !important;
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s ease !important;
    }
    .nav-link:hover {
        background: var(--sidebar-hover) !important;
        color: #fff !important;
        transform: translateX(5px);
    }
    .nav-link i {
        width: 24px;
        text-align: center;
        margin-right: 12px;
        font-size: 1.1rem;
        opacity: 0.8;
    }
    .nav-link.active {
        background: var(--sidebar-accent) !important;
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2) !important;
    }

    /* TOGGLE BUTTON (DESKTOP ONLY) */
    .sidebar .text-center.d-md-inline {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 1rem 0;
        background: var(--sidebar-bg-end);
        border-top: 1px solid rgba(255,255,255,0.05);
    }
    @media (max-width: 768px) {
        .sidebar .text-center.d-md-inline { display: none !important; }
    }

    /* MOBILE QUICK MENU (NEW SELECTOR) */
    .mobile-quick-menu {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        height: 100dvh; /* Dynamic viewport height for mobile */
        background: rgba(4, 25, 32, 0.99);
        z-index: 10001 !important;
        display: flex;
        flex-direction: column;
        transform: translateY(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        padding: env(safe-area-inset-top) 20px env(safe-area-inset-bottom);
        visibility: hidden;
        overscroll-behavior: contain;
        pointer-events: all !important;
    }

    .mobile-quick-menu.active {
        transform: translateY(0);
        visibility: visible;
    }

    .mobile-menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px 0;
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }

    .menu-brand {
        color: #fff !important;
        font-size: 1.3rem;
        font-weight: 800;
        letter-spacing: 1px;
        opacity: 1 !important;
    }

    .btn-close-menu {
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff !important;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 1 !important;
    }

    .mobile-menu-content {
        flex: 1 1 auto;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch; /* Smooth scroll for iOS */
        padding: 20px 0;
        height: 100%;
        width: 100%;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        padding-bottom: 40px; /* Extra space at bottom to ensure last items are reachable */
    }

    .menu-card {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        padding: 15px 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        transition: all 0.2s ease;
        aspect-ratio: 1/1;
    }

    .menu-card:active {
        transform: scale(0.95);
        background: rgba(255,255,255,0.15);
    }

    .menu-card.active {
        background: var(--sidebar-accent);
        border-color: rgba(255,255,255,0.4);
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    .card-icon {
        font-size: 1.6rem;
        color: #fff !important;
        margin-bottom: 8px;
        opacity: 1 !important;
    }

    .card-icon.icon-ia {
        color: #00ff9d !important;
        text-shadow: 0 0 10px rgba(0, 255, 157, 0.4);
    }

    .card-label {
        color: #fff !important;
        font-size: 0.72rem;
        font-weight: 600;
        text-align: center;
        line-height: 1.1;
        opacity: 1 !important;
    }

    .mobile-menu-footer {
        padding: 20px 0;
        text-align: center;
        color: rgba(255,255,255,0.5);
        font-size: 0.8rem;
    }

    body.mobile-menu-open {
        overflow: hidden !important;
    }

    /* Force hide the standard sidebar overlay when the quick menu is active */
    body.mobile-menu-open .sidebar-overlay {
        display: none !important;
    }


    #content {
        flex: 1 0 auto;
    }


    /* Premium Global Shadow Override */
    .shadow {
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1) !important;
    }

    /* Standardized Card Shadow & Aesthetics */
    .card {
        border-radius: 12px !important;
        border: none !important;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
    }

    .card.shadow {
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1) !important;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15) !important;
    }

    /* Top Bar Card Transformation */
    .topbar {
        border-radius: 12px !important;
        border: none !important;
        margin: 10px 23px 20px 23px !important;
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1) !important;
        transition: all 0.3s ease !important;
    }

    /* Premium Filter Styling - Standardized UI */
    .premium-filter {
        border-radius: 10px !important;
        height: 45px !important;
        padding: 5px 15px !important;
        border: 1px solid #d1d3e2 !important;
        background-color: #fff !important;
        color: #6e707e !important;
        font-size: 0.85rem !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
    }

    .premium-filter:focus {
        border-color: #4e73df !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25) !important;
        background-color: #fff !important;
    }

    select.premium-filter {
        cursor: pointer;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
        padding-right: 2.5rem !important;
    }

    /* Table Global Refresh - Larger Fonts & Harmony */
    .table {
        font-size: 0.95rem !important;
        color: #2c404a !important;
    }

    .table thead th {
        font-size: 0.8rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
        font-weight: 700 !important;
        color: #4e73df !important;
        padding: 15px 10px !important;
        vertical-align: middle !important;
        border-top: none !important;
    }

    .table tbody td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
    }

    .table-sm th, .table-sm td {
        padding: 10px 8px !important;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    /* Zebra Striping & Interactive Rows - High Quality Readability */
    .table tbody tr:nth-child(even) {
        background-color: rgba(6, 34, 43, 0.02) !important;
    }

    .table tbody tr:hover {
        background-color: rgba(6, 34, 43, 0.05) !important;
        transition: background-color 0.15s ease-in-out;
        cursor: default;
    }

    .form-inline .form-group {
        margin-bottom: 0px !important;
    }

    /* Global Dot Badge Style - Refined & Discreet */
    .dot-badge {
        font-size: 0.82rem;
        padding: 4px 12px;
        border-radius: 30px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: none !important;
    }
    
    .dot-badge.badge-success { background-color: #1cc88a !important; color: #fff !important; }
    .dot-badge.badge-danger { background-color: #e74a3b !important; color: #fff !important; }
    .dot-badge.badge-warning { background-color: #f6c23e !important; color: #fff !important; }
    .dot-badge.badge-info { background-color: #36b9cc !important; color: #fff !important; }
    .dot-badge.badge-secondary { background-color: #858796 !important; color: #fff !important; }
    
    .dot-badge::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #fff;
        box-shadow: 0 0 0 1.5px rgba(255, 255, 255, 0.3);
    }

    /* Premium Status Badge - Refined & Proportional */
    .status-badge {
        font-size: 0.81rem;
        padding: 8px 18px !important;
        min-width: 130px !important;
        border-radius: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .status-badge:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .status-badge.badge-success { background-color: #1cc88a !important; color: #fff !important; }
    .status-badge.badge-danger { background-color: #e74a3b !important; color: #fff !important; }
    .status-badge.badge-warning { background-color: #f6c23e !important; color: #fff !important; }
    .status-badge.badge-info { background-color: #36b9cc !important; color: #fff !important; }
    .status-badge.badge-secondary { background-color: #858796 !important; color: #fff !important; }

    /* Botão Premium para Topo de Lista (Cadastrar Novo) */
    .btn-premium-cadastro {
        background-color: rgb(44, 64, 74) !important;
        border-radius: 10px !important;
        border: none !important;
        height: 50px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #fff !important;
        font-weight: 700 !important;
        text-transform: none !important; /* Mantém capitalize do PHP se necessário */
        transition: all 0.3s ease !important;
        padding: 0 25px !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        min-width: 180px !important; /* Mantém o tamanho robusto original */
    }

    .btn-premium-cadastro:hover {
        background-color: #3e5b69 !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important;
        color: #fff !important;
        text-decoration: none !important;
    }

    .btn-premium-import {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
        border-radius: 12px !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        height: 50px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #fff !important;
        font-weight: 700 !important;
        text-transform: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        padding: 0 25px !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        cursor: pointer !important;
        position: relative;
        overflow: hidden;
    }

    .btn-premium-import:hover {
        background: linear-gradient(135deg, #5a6268 0%, #343a40 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2) !important;
        color: #fff !important;
        text-decoration: none !important;
    }

    .btn-premium-import::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: 0.5s;
    }

    .btn-premium-import:hover::after {
        left: 100%;
    }

    /* Correção Modais Summernote - Z-index e Estabilidade */
    .note-modal {
        z-index: 10050 !important;
    }
    .note-modal-backdrop {
        z-index: 10040 !important;
    }
</style>
<div class="sidebar-overlay" onclick="document.querySelector('#sidebarToggleTop').click()"></div>
<?php include_once 'pagination_style.php'; ?>