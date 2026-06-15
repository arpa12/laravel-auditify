<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auditify Dashboard')</title>
    <!-- Dynamic theme script to prevent FOUC (flash of unstyled content) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('auditify-theme') || '{{ config('auditify.theme', 'dark') }}';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <!-- Modern Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --font-sans: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --border-radius: 12px;
            --transition-smooth: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Light Theme Variables */
        [data-theme="light"] {
            --bg-primary: #f3f4f6; /* Gray 100 */
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-input: #f9fafb;
            --text-primary: #1f2937; /* Gray 800 */
            --text-secondary: #4b5563; /* Gray 600 */
            --text-muted: #9ca3af;
            --border-color: #e5e7eb; /* Gray 200 */
            --border-hover: #cbd5e1; /* Gray 300 */
            
            --accent-create: #059669;
            --accent-create-bg: rgba(5, 150, 105, 0.08);
            --accent-update: #2563eb;
            --accent-update-bg: rgba(37, 99, 235, 0.08);
            --accent-delete: #dc2626;
            --accent-delete-bg: rgba(220, 38, 38, 0.08);
            --accent-other: #d97706;
            --accent-other-bg: rgba(217, 119, 6, 0.08);

            --primary-glow: rgba(37, 99, 235, 0.08);
            --color-indigo: #2563eb;
            --color-indigo-hover: #1d4ed8;
            --title-gradient: linear-gradient(to right, #111827, #4b5563);
            
            --card-text-highlight: #111827;
            --table-header-bg: #f9fafb;
            --table-row-hover: #f8fafc;
            --kpi-text-color: #111827;
            --box-bg-light: #f9fafb;
            --progress-bg: #e5e7eb;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);

            --diff-modified-bg: rgba(37, 99, 235, 0.06);
            --diff-added-bg: rgba(5, 150, 105, 0.06);
            --diff-removed-bg: rgba(220, 38, 38, 0.06);
            --diff-removed-color: #dc2626;
            --diff-added-color: #059669;

            --code-terminal-bg: #f9fafb;
            --code-terminal-color: #059669;
            --nav-hover-bg: rgba(0, 0, 0, 0.03);
            --dropdown-footer-bg: #f9fafb;
        }

        /* Dark Theme Variables */
        [data-theme="dark"] {
            --bg-primary: #090d16;
            --bg-secondary: #111827;
            --bg-card: #1f2937;
            --bg-input: #1f2937;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --border-color: rgba(255, 255, 255, 0.08);
            --border-hover: rgba(255, 255, 255, 0.15);
            
            --accent-create: #10b981;
            --accent-create-bg: rgba(16, 185, 129, 0.1);
            --accent-update: #3b82f6;
            --accent-update-bg: rgba(59, 130, 246, 0.1);
            --accent-delete: #ef4444;
            --accent-delete-bg: rgba(239, 68, 68, 0.1);
            --accent-other: #f59e0b;
            --accent-other-bg: rgba(245, 158, 11, 0.1);

            --primary-glow: rgba(59, 130, 246, 0.15);
            --color-indigo: #3b82f6;
            --color-indigo-hover: #2563eb;
            --title-gradient: linear-gradient(to right, #ffffff, #9ca3af);

            --card-text-highlight: #ffffff;
            --table-header-bg: rgba(255, 255, 255, 0.02);
            --table-row-hover: rgba(255, 255, 255, 0.01);
            --kpi-text-color: #ffffff;
            --box-bg-light: rgba(255, 255, 255, 0.02);
            --progress-bg: rgba(255, 255, 255, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);

            --diff-modified-bg: rgba(59, 130, 246, 0.1);
            --diff-added-bg: rgba(16, 185, 129, 0.1);
            --diff-removed-bg: rgba(239, 68, 68, 0.1);
            --diff-removed-color: #f87171;
            --diff-added-color: #34d399;

            --code-terminal-bg: #030712;
            --code-terminal-color: #34d399;
            --nav-hover-bg: rgba(255, 255, 255, 0.03);
            --dropdown-footer-bg: rgba(0, 0, 0, 0.2);
        }

        /* Theme toggle icon display based on active theme */
        [data-theme="light"] .theme-icon-moon {
            display: block !important;
        }
        [data-theme="dark"] .theme-icon-sun {
            display: block !important;
        }

        /* Reset and Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Webkit Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--bg-card);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* Layout Structure */
        .app-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 24px;
            flex-shrink: 0;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            text-decoration: none;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--color-indigo), #0ea5e9);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }

        .brand-logo svg {
            width: 20px;
            height: 20px;
            fill: white;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-badge {
            font-size: 10px;
            background-color: rgba(99, 102, 241, 0.15);
            color: var(--color-indigo);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition-smooth);
        }

        .nav-item a:hover {
            color: var(--text-primary);
            background-color: var(--nav-hover-bg);
            transform: translateX(4px);
        }

        .nav-item.active a {
            color: white;
            background-color: var(--color-indigo);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .sidebar-badge {
            background-color: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 9999px;
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
        }

        /* Main Workspace Container */
        .main-wrapper {
            margin-left: 260px;
            flex-grow: 1;
            padding: 40px;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* Page Headers */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.75px;
            background: var(--title-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Notification Bell component styles */
        .notification-bell-container {
            position: relative;
            display: inline-block;
        }
        .bell-btn {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition-smooth);
            position: relative;
        }
        .bell-btn option {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        select option {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        .bell-btn:hover {
            color: var(--text-primary);
            border-color: var(--border-hover);
            background-color: var(--nav-hover-bg);
        }
        .bell-btn svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
        .bell-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444;
            border: 1.5px solid var(--bg-secondary);
        }
        .bell-dropdown {
            position: absolute;
            top: 48px;
            right: 0;
            width: 320px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1000;
            overflow: hidden;
            animation: dropdownFade 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        /* Toast Notification Styling */
        #toastContainer {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 380px;
            width: 100%;
        }

        .toast-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent-delete);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 12px;
            transform: translateX(120%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            opacity: 0;
            pointer-events: auto;
        }

        .toast-card.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-card.toast-severity-critical { border-left-color: #ef4444; }
        .toast-card.toast-severity-high { border-left-color: #f97316; }
        .toast-card.toast-severity-medium { border-left-color: #eab308; }
        .toast-card.toast-severity-low { border-left-color: #3b82f6; }

        .toast-icon {
            font-size: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .toast-content {
            flex-grow: 1;
            min-width: 0;
        }

        .toast-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .toast-body {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.4;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .toast-actions {
            display: flex;
            gap: 8px;
        }

        .toast-link {
            font-size: 11px;
            font-weight: 700;
            color: var(--color-indigo);
            text-decoration: none;
            cursor: pointer;
        }

        .toast-link:hover {
            color: var(--color-indigo-hover);
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            padding: 0 4px;
            align-self: flex-start;
        }

        .toast-close:hover {
            color: var(--text-primary);
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .bell-dropdown.show {
            display: block;
        }
        .bell-dropdown-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bell-dropdown-list {
            max-height: 280px;
            overflow-y: auto;
            list-style: none;
        }
        .bell-dropdown-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 12px;
            text-decoration: none;
            transition: var(--transition-smooth);
        }
        .bell-dropdown-item:hover {
            background-color: var(--nav-hover-bg);
        }
        .bell-dropdown-item:last-child {
            border-bottom: none;
        }
        .bell-dropdown-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 4px;
            flex-shrink: 0;
        }
        .bell-dropdown-info {
            flex-grow: 1;
            min-width: 0;
        }
        .bell-dropdown-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bell-dropdown-desc {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .bell-dropdown-time {
            font-size: 10px;
            color: var(--text-muted);
            margin-top: 4px;
        }
        .bell-dropdown-footer {
            padding: 12px;
            text-align: center;
            border-top: 1px solid var(--border-color);
            background-color: var(--dropdown-footer-bg);
        }
        .bell-dropdown-footer a {
            font-size: 12px;
            font-weight: 600;
            color: var(--color-indigo);
            text-decoration: none;
            transition: var(--transition-smooth);
        }
        .bell-dropdown-footer a:hover {
            color: var(--color-indigo-hover);
        }

        .indicator-critical { background-color: #ef4444; box-shadow: 0 0 8px #ef4444; }
        .indicator-high { background-color: #f97316; box-shadow: 0 0 8px #f97316; }
        .indicator-medium { background-color: #eab308; box-shadow: 0 0 8px #eab308; }
        .indicator-low { background-color: #3b82f6; box-shadow: 0 0 8px #3b82f6; }

        /* Base Card Component */
        .card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 24px;
            box-shadow: var(--shadow-md);
            transition: var(--transition-smooth);
            min-width: 0;
            overflow: hidden;
        }

        .card:hover {
            border-color: var(--border-hover);
        }

        /* Buttons styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: var(--transition-smooth);
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--color-indigo);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--color-indigo-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .btn-secondary {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            border-color: var(--border-hover);
            background-color: rgba(255, 255, 255, 0.05);
            transform: translateY(-1px);
        }

        /* Badges for actions */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-create {
            background-color: var(--accent-create-bg);
            color: var(--accent-create);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-update {
            background-color: var(--accent-update-bg);
            color: var(--accent-update);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .badge-delete {
            background-color: var(--accent-delete-bg);
            color: var(--accent-delete);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-other {
            background-color: var(--accent-other-bg);
            color: var(--accent-other);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        /* Global UI Elements Fade-In */
        .fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Header and Styling */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 0 20px;
            align-items: center;
            z-index: 101;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .mobile-menu-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 6px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-right: 16px;
            transition: var(--transition-smooth);
        }

        .mobile-menu-btn:hover {
            border-color: var(--border-hover);
            background-color: var(--nav-hover-bg);
        }

        .mobile-menu-btn svg {
            width: 20px;
            height: 20px;
        }

        .mobile-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .mobile-brand-logo {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, var(--color-indigo), #0ea5e9);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-brand-logo svg {
            width: 16px;
            height: 16px;
            fill: white;
        }

        .mobile-brand-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 102;
        }

        .card-table-wrapper, .table-container, .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .mobile-header {
                display: flex;
            }
            .sidebar {
                position: fixed;
                top: 0;
                left: -260px; /* Hide offscreen */
                width: 260px;
                height: 100vh;
                z-index: 105;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .sidebar.active {
                transform: translateX(260px); /* Slide in */
            }
            .sidebar-overlay.active {
                display: block;
            }
            .main-wrapper {
                margin-left: 0;
                padding: 84px 20px 24px; /* Give room for mobile top header */
            }
            .hide-on-tablet {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .page-title {
                font-size: 24px;
            }
            .header-actions {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 8px;
            }
            .hide-on-mobile {
                display: none !important;
            }
            .paginator-container {
                flex-direction: column;
                gap: 16px;
                text-align: center;
                align-items: center;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="app-container">
        <!-- Mobile Top Header -->
        <div class="mobile-header">
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="{{ url(config('auditify.route_prefix', 'auditify')) }}" class="mobile-brand">
                <div class="mobile-brand-logo">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/>
                    </svg>
                </div>
                <span class="mobile-brand-name">Auditify</span>
            </a>
        </div>
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <a href="{{ url(config('auditify.route_prefix', 'auditify')) }}" class="brand">
                <div class="brand-logo">
                    <!-- Dashboard icon -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/>
                    </svg>
                </div>
                <span class="brand-name">Auditify <span class="brand-badge">v1.0.1</span></span>
            </a>

            <nav style="flex-grow: 1;">
                <ul class="nav-menu">
                    @php
                        $currentPath = request()->path();
                        $prefix = trim(config('auditify.route_prefix', 'auditify'), '/');
                        $isDashboard = ($currentPath === $prefix);
                        $isActionLogs = str_starts_with($currentPath, $prefix . '/action-logs');
                        $isActivityLogs = str_starts_with($currentPath, $prefix . '/activity-logs');
                        $isSecurityLogs = str_starts_with($currentPath, $prefix . '/security-logs');
                        $isReports = str_starts_with($currentPath, $prefix . '/reports');
                        
                        $unreadSecurityCount = \Auditify\Models\SecurityLog::unread()->count();
                    @endphp
                    
                    <!-- Dashboard -->
                    <li class="nav-item {{ $isDashboard ? 'active' : '' }}">
                        <a href="{{ url($prefix) }}">
                            <!-- Home Icon -->
                            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Module 1: Action Logs -->
                    <li class="nav-item {{ $isActionLogs ? 'active' : '' }}">
                        <a href="{{ url($prefix . '/action-logs') }}">
                            <!-- Table Changes Icon -->
                            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 14h6v-6H4v6zm0 5h6v-6H4v6zM4 9h6V3H4v6zm9 5h7v-2h-7v2zm0 5h7v-2h-7v2zM13 5v2h7V5h-7z"/>
                            </svg>
                            <span>Action Logs</span>
                        </a>
                    </li>

                    <!-- Module 2: Activity Logs -->
                    <li class="nav-item {{ $isActivityLogs ? 'active' : '' }}">
                        <a href="{{ url($prefix . '/activity-logs') }}">
                            <!-- Activity Tracker Icon -->
                            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                            </svg>
                            <span>Activity Logs</span>
                        </a>
                    </li>

                    <!-- Module 3: Security Logs -->
                    <li class="nav-item {{ $isSecurityLogs ? 'active' : '' }}" style="position: relative;">
                        <a href="{{ url($prefix . '/security-logs') }}">
                            <!-- Security Warnings Shield Icon -->
                            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 6h2v6h-2V7zm0 8h2v2h-2v-2z"/>
                            </svg>
                            <span>Security Logs</span>
                            @if($unreadSecurityCount > 0)
                                <span class="sidebar-badge">{{ $unreadSecurityCount }}</span>
                            @endif
                        </a>
                    </li>
                    
                    <!-- Reports -->
                    <li class="nav-item {{ $isReports ? 'active' : '' }}">
                        <a href="{{ url($prefix . '/reports') }}">
                            <!-- Chart/Report Icon -->
                            <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                            </svg>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                &copy; {{ date('Y') }} Arpa Nihan
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="main-wrapper">
            <div class="page-header">
                <div>
                    <h1 class="page-title">@yield('header_title', 'Auditify')</h1>
                </div>
                <div class="header-actions">
                    @php
                        $bellUnreadCount = \Auditify\Models\SecurityLog::unread()->count();
                        $bellAlerts = \Auditify\Models\SecurityLog::unread()->latest()->limit(5)->get();
                    @endphp

                    <!-- Time Format Selector Dropdown -->
                    <div style="position: relative; display: inline-flex; align-items: center; gap: 8px; margin-right: 8px;">
                        <span style="font-size: 12px; font-weight: 500; color: var(--text-secondary); white-space: nowrap;">Time Format:</span>
                        <select id="timeFormatSelect" onchange="changeTimeFormat(this.value)" class="bell-btn" style="width: auto; padding: 0 28px 0 12px; font-size: 12px; font-weight: 600; font-family: var(--font-sans); cursor: pointer; text-align: center; border-radius: 8px; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%25239CA3AF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 10px top 50%; background-size: 8px auto;">
                            <option value="24h">24 Hour</option>
                            <option value="12h">12 Hour</option>
                        </select>
                    </div>

                    <!-- Theme Toggle Button -->
                    <button class="bell-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Toggle Theme" style="margin-right: 8px;">
                        <!-- Sun Icon (shown in dark theme) -->
                        <svg class="theme-icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none; width: 20px; height: 20px;">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                        <!-- Moon Icon (shown in light theme) -->
                        <svg class="theme-icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none; width: 20px; height: 20px;">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                        </svg>
                    </button>
                    
                    <!-- Notification Bell Component -->
                    <div class="notification-bell-container">
                        <button class="bell-btn" id="bellBtn" onclick="toggleBellDropdown(event)">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                            </svg>
                            @if($bellUnreadCount > 0)
                                <span class="bell-badge"></span>
                            @endif
                        </button>
                        
                        <div class="bell-dropdown" id="bellDropdown">
                            <div class="bell-dropdown-header">
                                <span>Security Notifications</span>
                                @if($bellUnreadCount > 0)
                                    <span class="badge" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 10px;">{{ $bellUnreadCount }} New</span>
                                @endif
                            </div>
                            <ul class="bell-dropdown-list">
                                @forelse($bellAlerts as $alert)
                                    <li>
                                        <a href="{{ url($prefix . '/security-logs/' . $alert->id) }}" class="bell-dropdown-item">
                                            <div class="bell-dropdown-indicator indicator-{{ $alert->severity }}"></div>
                                            <div class="bell-dropdown-info">
                                                <div class="bell-dropdown-title">{{ $alert->title }}</div>
                                                <div class="bell-dropdown-desc">{{ $alert->description }}</div>
                                                <div class="bell-dropdown-time">{{ $alert->created_at->diffForHumans() }}</div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px;">
                                        No new unread security alerts.
                                    </li>
                                @endforelse
                            </ul>
                            <div class="bell-dropdown-footer">
                                <a href="{{ url($prefix . '/security-logs') }}">View All Alerts</a>
                            </div>
                        </div>
                    </div>

                    @yield('header_actions')
                </div>
            </div>

            <!-- Page Content -->
            <div class="fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('auditify-theme', newTheme);
            
            // Dispatch dynamic event so other components (e.g. Chart.js) can refresh colors
            const event = new CustomEvent('themeChanged', { detail: newTheme });
            window.dispatchEvent(event);
        }

        function toggleBellDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('bellDropdown');
            dropdown.classList.toggle('show');
        }

        let audioCtxInstance = null;

        function getAudioContext() {
            try {
                if (!audioCtxInstance) {
                    audioCtxInstance = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtxInstance.state === 'suspended') {
                    audioCtxInstance.resume();
                }
                return audioCtxInstance;
            } catch (e) {
                console.warn("Failed to initialize AudioContext:", e);
                return null;
            }
        }

        // Automatically resume AudioContext on first click or keypress on the page
        const resumeAudio = () => {
            getAudioContext();
            document.removeEventListener('click', resumeAudio);
            document.removeEventListener('keydown', resumeAudio);
        };
        document.addEventListener('click', resumeAudio);
        document.addEventListener('keydown', resumeAudio);

        function playNotificationSound() {
            try {
                const audioCtx = getAudioContext();
                if (!audioCtx) return;

                const playTones = () => {
                    const osc1 = audioCtx.createOscillator();
                    const osc2 = audioCtx.createOscillator();
                    const gainNode = audioCtx.createGain();
                    
                    osc1.type = 'sine';
                    osc2.type = 'sine';
                    
                    osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime); // C5
                    osc2.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.12); // E5
                    
                    gainNode.gain.setValueAtTime(0.12, audioCtx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.45);
                    
                    osc1.connect(gainNode);
                    osc2.connect(gainNode);
                    gainNode.connect(audioCtx.destination);
                    
                    osc1.start();
                    osc1.stop(audioCtx.currentTime + 0.15);
                    
                    osc2.start(audioCtx.currentTime + 0.12);
                    osc2.stop(audioCtx.currentTime + 0.45);
                };

                if (audioCtx.state === 'suspended') {
                    audioCtx.resume().then(() => {
                        if (audioCtx.state !== 'suspended') {
                            playTones();
                        } else {
                            console.warn("AudioContext remains suspended despite resume attempt.");
                        }
                    }).catch(err => {
                        console.warn("Failed to resume AudioContext dynamically:", err);
                    });
                } else {
                    playTones();
                }
            } catch (e) {
                console.warn("Web Audio API blocked or not supported", e);
            }
        }

        function formatTimestamp(isoString, format) {
            const date = new Date(isoString);
            if (isNaN(date.getTime())) return isoString;

            const pad = (num) => String(num).padStart(2, '0');
            const yyyy = date.getFullYear();
            const mm = pad(date.getMonth() + 1);
            const dd = pad(date.getDate());
            
            let hours = date.getHours();
            const minutes = pad(date.getMinutes());
            const seconds = pad(date.getSeconds());
            
            if (format === '12h') {
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // 0 is 12
                return `${yyyy}-${mm}-${dd} ${pad(hours)}:${minutes}:${seconds} ${ampm}`;
            } else {
                return `${yyyy}-${mm}-${dd} ${pad(hours)}:${minutes}:${seconds}`;
            }
        }

        function applyTimeFormat() {
            const format = localStorage.getItem('auditify-time-format') || '24h';
            const select = document.getElementById('timeFormatSelect');
            if (select) {
                select.value = format;
            }

            const elements = document.querySelectorAll('.audit-timestamp');
            elements.forEach(el => {
                const iso = el.getAttribute('data-timestamp');
                if (iso) {
                    el.textContent = formatTimestamp(iso, format);
                }
            });
        }

        function changeTimeFormat(format) {
            localStorage.setItem('auditify-time-format', format);
            applyTimeFormat();
        }

        let previousUnreadCount = parseInt(localStorage.getItem('auditify-last-heard-count') || '0', 10);
        const serverUnreadCount = {{ $bellUnreadCount }};

        // If the server has new alerts we haven't played sound for, prepare sound trigger
        if (serverUnreadCount > previousUnreadCount) {
            const playSoundOnGesture = () => {
                playNotificationSound();
                localStorage.setItem('auditify-last-heard-count', serverUnreadCount);
                previousUnreadCount = serverUnreadCount;
                document.removeEventListener('click', playSoundOnGesture);
                document.removeEventListener('keydown', playSoundOnGesture);
            };
            document.addEventListener('click', playSoundOnGesture);
            document.addEventListener('keydown', playSoundOnGesture);
            
            // Try immediately in case audio is already unlocked
            setTimeout(() => {
                playNotificationSound();
                localStorage.setItem('auditify-last-heard-count', serverUnreadCount);
                previousUnreadCount = serverUnreadCount;
            }, 100);
        } else {
            localStorage.setItem('auditify-last-heard-count', serverUnreadCount);
            previousUnreadCount = serverUnreadCount;
        }

        function pollUnreadAlerts() {
            const checkUrl = "{{ url(config('auditify.route_prefix', 'auditify') . '/security-logs/unread-check') }}";
            fetch(checkUrl)
                .then(response => response.json())
                .then(data => {
                    const currentCount = data.unread_count;
                    
                    if (currentCount > previousUnreadCount) {
                        playNotificationSound();
                        localStorage.setItem('auditify-last-heard-count', currentCount);
                        
                        // Push dynamic toast alerts
                        if (data.recent_alerts && data.recent_alerts.length > 0) {
                            showToastNotification(data.recent_alerts[0]);
                            showBrowserNotification(data.recent_alerts[0]);
                        }
                    }
                    
                    if (currentCount !== previousUnreadCount) {
                        updateBellBadge(currentCount);
                        updateBellDropdownList(data.recent_alerts, currentCount);
                    }
                    
                    previousUnreadCount = currentCount;
                })
                .catch(err => console.error("Error polling alerts:", err));
        }

        function showToastNotification(alert) {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast-card toast-severity-${alert.severity}`;
            const severityEmoji = alert.severity === 'critical' || alert.severity === 'high' ? '⚠️' : '🛡️';

            toast.innerHTML = `
                <div class="toast-icon">${severityEmoji}</div>
                <div class="toast-content">
                    <div class="toast-title">${alert.title}</div>
                    <div class="toast-body">${alert.description}</div>
                    <div class="toast-actions">
                        <a href="${alert.url}" class="toast-link">View Details</a>
                    </div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 400);
            }, 6000);
        }

        function showBrowserNotification(alert) {
            if (!("Notification" in window)) return;
            
            if (Notification.permission === "granted") {
                new Notification(`Auditify Security Alert: ${alert.title}`, {
                    body: alert.description
                });
            }
        }

        function updateBellBadge(count) {
            const bellBtn = document.getElementById('bellBtn');
            if (!bellBtn) return;
            
            let badge = bellBtn.querySelector('.bell-badge');
            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'bell-badge';
                    bellBtn.appendChild(badge);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }

            // Update sidebar unread badge if exists
            const sidebarBadge = document.querySelector('.sidebar-badge');
            if (sidebarBadge) {
                if (count > 0) {
                    sidebarBadge.textContent = count;
                    sidebarBadge.style.display = 'inline-flex';
                } else {
                    sidebarBadge.style.display = 'none';
                }
            }
        }

        function updateBellDropdownList(alerts, count) {
            const listContainer = document.querySelector('.bell-dropdown-list');
            const header = document.querySelector('.bell-dropdown-header');
            if (!listContainer || !header) return;

            // Update header badge
            let headerBadge = header.querySelector('.badge');
            if (count > 0) {
                if (!headerBadge) {
                    headerBadge = document.createElement('span');
                    headerBadge.className = 'badge';
                    headerBadge.style.cssText = 'background-color: rgba(239, 68, 68, 0.15); color: #ef4444; font-size: 10px;';
                    header.appendChild(headerBadge);
                }
                headerBadge.textContent = `${count} New`;
            } else {
                if (headerBadge) {
                    headerBadge.remove();
                }
            }

            // Update items
            if (alerts.length === 0) {
                listContainer.innerHTML = `<li style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 12px;">No new unread security alerts.</li>`;
            } else {
                listContainer.innerHTML = '';
                alerts.forEach(alert => {
                    const li = document.createElement('li');
                    li.innerHTML = `
                        <a href="${alert.url}" class="bell-dropdown-item">
                            <div class="bell-dropdown-indicator indicator-${alert.severity}"></div>
                            <div class="bell-dropdown-info">
                                <div class="bell-dropdown-title">${alert.title}</div>
                                <div class="bell-dropdown-desc">${alert.description}</div>
                                <div class="bell-dropdown-time">${alert.time_ago}</div>
                            </div>
                        </a>
                    `;
                    listContainer.appendChild(li);
                });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyTimeFormat();
            
            // Request native browser desktop alert permissions
            if ("Notification" in window && Notification.permission === "default") {
                Notification.requestPermission();
            }

            // Trigger instant query check to sync state on mount
            pollUnreadAlerts();
            
            // Poll for new security alerts at the configured interval
            @php
                $pollingInterval = config('auditify.security_polling_interval', 60);
            @endphp
            @if($pollingInterval > 0)
                setInterval(pollUnreadAlerts, {{ $pollingInterval * 1000 }});
            @endif

            // Mobile Sidebar toggler
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (mobileMenuBtn && sidebar && overlay) {
                mobileMenuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                });
                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });

                // Close sidebar when navigation link is clicked
                const sidebarLinks = sidebar.querySelectorAll('.nav-item a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                    });
                });
            }
        });

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('bellDropdown');
            const bellBtn = document.getElementById('bellBtn');
            if (dropdown && dropdown.classList.contains('show')) {
                if (!dropdown.contains(event.target) && !bellBtn.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>
    <div id="toastContainer"></div>
    @yield('scripts')
</body>
</html>
