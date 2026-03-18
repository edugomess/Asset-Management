<style>
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

    nav.sidebar {
        position: fixed !important;
        top: 0;
        left: 0;
        bottom: 0;
        width: 15rem !important;
        background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-end) 100%) !important;
        box-shadow: 10px 0 30px rgba(88, 109, 133, 0.3) !important;
        border-right: 0px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 1050;
        min-height: 100vh !important;
        overflow: visible !important; /* Allow hover effects to cross sidebar boundary */
        padding-bottom: 0 !important;
        padding-top: 0 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    ul.sidebar {
        position: relative !important;
        width: 100% !important;
        flex: 1 1 auto !important;
        max-height: calc(100vh - 6rem - 5rem) !important; /* viewport - brand header - toggle button */
        overflow-y: auto !important;
        overflow-x: visible !important;
        margin-top: 0 !important;
        margin-bottom: 5rem !important; /* Space for fixed toggle button */
        background: transparent !important;
        box-shadow: none !important;
        z-index: 1;
    }

    ul.sidebar::-webkit-scrollbar {
        width: 4px;
    }

    ul.sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    /* Fixed toggle container at bottom */
    .sidebar .text-center.d-md-inline {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 15rem;
        background: linear-gradient(90deg, var(--sidebar-bg-end) 0%, var(--sidebar-bg-end) 100%);
        padding: 1rem 0;
        z-index: 1051;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        display: block !important; /* Ensure it shows up if it was d-none */
    }

    .sidebar.toggled .text-center.d-md-inline {
        width: 6.5rem;
    }

    /* Sidebar Toggle Styling */
    #sidebarToggle {
        width: 2.5rem;
        height: 2.5rem;
        text-align: center;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        transition: all 0.3s !important;
        position: relative;
    }


    /* Scrollbar now handled by ul.sidebar */

    .sidebar-brand {
        position: sticky !important;
        top: 0;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        text-decoration: none !important;
        padding: 0 !important; /* Removemos padding para controlar via height */
        height: 6rem !important; /* Altura fixa para garantir centralização vertical */
        margin-bottom: 0.5rem !important;
        background: var(--sidebar-bg-start) !important;
        z-index: 1052;
        transition: all 0.3s ease;
        width: 100%;
    }

    .sidebar-brand-icon {
        font-size: 1.8rem !important;
        color: #fff !important;
    }

    .sidebar-brand-text {
        font-weight: 800 !important;
        letter-spacing: 2px !important;
        font-size: 1.1rem !important;
        color: #fff !important;
        text-transform: uppercase;
        margin-left: 0 !important; /* Removido para não quebrar o justify-content: center */
        line-height: 1 !important; /* Garante alinhamento vertical com o ícone */
    }

    .sidebar-heading {
        color: rgb(255, 255, 255) !important;
        font-size: 0.65rem !important;
        font-weight: 800 !important;
        text-transform: uppercase;
        padding: 0.8rem 1.5rem 0.3rem 1.5rem !important;
        letter-spacing: 2.5px;
        opacity: 0.8;
    }

    .nav-item {
        position: relative;
        margin: 0.15rem 0.8rem !important;
        z-index: 1;
    }

    .nav-item:hover {
        z-index: 1060 !important;
    }

    .nav-link {
        padding: 0.65rem 1.2rem !important;
        border-radius: 14px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        font-weight: 500 !important;
        font-size: 0.92rem !important;
        color: var(--sidebar-text) !important;
        display: flex !important;
        align-items: center !important;
        border: 1px solid transparent !important;
        overflow: visible !important; /* Allow shadow to leak into the strip */
        white-space: nowrap !important;
    }

    .nav-link i {
        width: 26px;
        text-align: center;
        margin-right: 14px;
        font-size: 1.15rem;
        transition: all 0.3s ease;
        opacity: 0.7;
    }

    .nav-link:hover {
        background: var(--sidebar-hover) !important;
        color: #fff !important;
        transform: translateX(6px);
        backdrop-filter: blur(8px);
        border-color: rgba(255, 255, 255, 0.1) !important;
        position: relative;
        z-index: 1060 !important;
    }

    .nav-link:hover i {
        opacity: 1;
        transform: scale(1.15) rotate(5deg);
        color: var(--sidebar-accent);
    }

    .nav-link.active {
        background: linear-gradient(135deg, var(--sidebar-accent) 0%, #153a49 100%) !important;
        color: #ffffffd5 !important;
        box-shadow: 0 10px 25px var(--sidebar-accent-glow) !important;
        font-weight: 600 !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
        z-index: 10;
    }

    .nav-link.active i {
        opacity: 1;
        color: #fff !important;
        transform: scale(1.1);
    }

    /* Active Indicator Pulse */
    .nav-link.active::after {
        content: '';
        position: absolute;
        right: 1.2rem;
        width: 6px;
        height: 6px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 0 10px #fff;
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.5);
            opacity: 0.5;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .sidebar-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        margin: 1.25rem 1.2rem 0.25rem !important;
        width: calc(100% - 2.4rem);
    }

    /* Global Table Fixes - Optimized for Asset Management */
    .table-responsive {
        overflow-x: hidden !important;
        /* Prevent horizontal scrollbars */
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .table {
        margin-bottom: 0 !important;
        width: 100% !important;
    }

    .table th {
        background: #f8f9fc;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #4e73df;
        border-top: none !important;
        padding: 1rem !important;
    }

    .table td {
        padding: 1rem !important;
        vertical-align: middle !important;
        border-top: 1px solid #e3e6f0 !important;
        color: #5a5c69;
        font-size: 0.85rem;
    }

    .table tr:hover {
        background-color: #f8f9fc;
    }

    /* Sidebar Toggle Styling */
    #sidebarToggle {
        width: 2.5rem;
        height: 2.5rem;
        text-align: center;
        margin-bottom: 1rem;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        transition: all 0.3s !important;
        position: relative;
    }

    #sidebarToggle::after {
        font-weight: 900;
        content: "\f104";
        font-family: 'Font Awesome 5 Free';
        margin-right: 0.1rem;
        color: rgba(255, 255, 255, 0.5);
    }

    .sidebar.toggled #sidebarToggle::after {
        content: "\f105";
    }

    #sidebarToggle:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        transform: scale(1.1);
    }

    #sidebarToggle:hover::after {
        color: #fff;
    }



    /* Toggled State Adjustments */
    nav.sidebar.toggled {
        width: 6.5rem !important;
    }

    .sidebar.toggled .nav-item {
        margin: 0.3rem 0.6rem !important;
    }

    .sidebar.toggled .nav-link {
        justify-content: center !important;
        padding: 1rem 0 !important;
        border-radius: 12px !important;
    }

    .sidebar.toggled .nav-link i {
        margin-right: 0 !important;
        font-size: 1.4rem;
    }

    .sidebar.toggled .nav-link span,
    .sidebar.toggled .sidebar-heading,
    .sidebar.toggled .sidebar-brand-text,
    .sidebar.toggled .nav-link::after {
        display: none !important;
    }

    /* Sticky Footer Global */
    #wrapper {
        display: flex;
        min-height: 100vh;
    }

    #content-wrapper {
        display: flex;
        flex-direction: column;
        width: 100%;
        flex: 1;
        margin-left: 15rem; /* Sidebar width */
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar.toggled+#content-wrapper {
        margin-left: 6.5rem;
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

    .form-inline .form-group {
        margin-bottom: 0px !important;
    }
</style>
<?php include_once 'pagination_style.php'; ?>