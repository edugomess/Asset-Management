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
    }

    .sidebar {
        background: linear-gradient(180deg, var(--sidebar-bg-start) 0%, var(--sidebar-bg-end) 100%) !important;
        box-shadow: 10px 0 30px rgba(88, 109, 133, 0.3) !important;
        border-right: 0px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 1050;
    }

    .sidebar-brand {
        padding: 1.0rem 1rem 0.2rem 1rem !important;
        margin-bottom: 0.0rem;
    }

    .sidebar-brand-text {
        font-weight: 800;
        letter-spacing: 2.5px;
        font-size: 1.25rem;
        background: linear-gradient(135deg, #fff 0%, #ffffff 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
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
        overflow: hidden;
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
    }

    /* Sidebar Toggle Styling */
    #sidebarToggle {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        transition: all 0.3s !important;
        margin-top: 1.5rem;
    }

    #sidebarToggle:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        transform: scale(1.1);
    }

    /* Toggled State Adjustments */
    .sidebar.toggled {
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
</style>
<?php include_once 'pagination_style.php'; ?>