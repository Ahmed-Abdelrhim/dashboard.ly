<x-filament-panels::page>
    <style>
        /* Scoped Profile Styles */
        :root {
            --p-primary: #f59e0b;
            --p-primary-hover: #d97706;
            --p-primary-ring: rgba(245, 158, 11, 0.2);
            --p-bg-card: #ffffff;
            --p-bg-subtle: #f9fafb;
            --p-border: #e5e7eb;
            --p-text-main: #111827;
            --p-text-muted: #6b7280;
            --p-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.08);
            --p-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.08);
        }

        .dark {
            --p-primary: #fbbf24;
            --p-primary-hover: #f59e0b;
            --p-primary-ring: rgba(251, 191, 36, 0.2);
            --p-bg-card: #18181b;
            --p-bg-subtle: #27272a;
            --p-border: #3f3f46;
            --p-text-main: #f4f4f5;
            --p-text-muted: #a1a1aa;
            --p-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3);
            --p-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }

        .p-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        /* Tabs Card */
        .p-tabs-wrap {
            background: var(--p-bg-card);
            border: 1px solid var(--p-border);
            border-radius: 1rem;
            padding: 0.375rem;
            box-shadow: var(--p-shadow);
        }

        /* 2-Column Grid for Tab 1 */
        .p-profile-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 1.75rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .p-profile-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Modern Cards */
        .p-card {
            background: var(--p-bg-card);
            border: 1px solid var(--p-border);
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: var(--p-shadow);
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
            box-sizing: border-box;
            width: 100%;
        }

        .p-card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--p-border);
        }

        .p-card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--p-text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.25rem 0;
        }

        .p-card-desc {
            font-size: 0.85rem;
            color: var(--p-text-muted);
            margin: 0;
            line-height: 1.4;
        }

        /* Avatar in Sidebar */
        .p-avatar-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 2rem 1.5rem;
        }

        .p-avatar-wrap {
            position: relative;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            box-shadow: 0 8px 20px -4px rgba(245, 158, 11, 0.35);
            margin-bottom: 1.25rem;
        }

        .p-avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            background: var(--p-bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .p-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .p-avatar-initials {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            user-select: none;
        }

        .p-avatar-loading {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            z-index: 10;
        }

        .p-user-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--p-text-main);
            margin: 0;
        }

        .p-user-email {
            font-size: 0.85rem;
            color: var(--p-text-muted);
            margin: 0.25rem 0 1rem 0;
            word-break: break-all;
        }

        .p-pill-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .p-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .p-badge-role {
            background: rgba(245, 158, 11, 0.12);
            color: #b45309;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }

        .dark .p-badge-role {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .p-badge-2fa {
            background: rgba(16, 185, 129, 0.12);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .dark .p-badge-2fa {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }

        .p-meta-divider {
            width: 100%;
            height: 1px;
            background: var(--p-border);
            margin: 0 0 1rem 0;
        }

        .p-meta-row {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--p-text-muted);
        }

        .p-meta-val {
            font-weight: 600;
            color: var(--p-text-main);
        }

        /* Avatar Upload Box in Main Form */
        .p-upload-box {
            padding: 1.25rem;
            border-radius: 0.875rem;
            border: 1px dashed var(--p-border);
            background: var(--p-bg-subtle);
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .p-upload-box {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .p-upload-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .p-upload-thumb {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid var(--p-border);
            background: var(--p-bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--p-shadow);
        }

        .p-upload-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .p-upload-thumb .p-thumb-initials {
            font-size: 1.1rem;
            font-weight: 700;
            color: #ffffff;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .p-upload-text h4 {
            margin: 0;
            font-size: 0.925rem;
            font-weight: 600;
            color: var(--p-text-main);
        }

        .p-upload-text p {
            margin: 0.2rem 0 0 0;
            font-size: 0.775rem;
            color: var(--p-text-muted);
        }

        .p-upload-actions {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex-shrink: 0;
        }

        .p-btn-upload {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--p-text-main);
            background: var(--p-bg-card);
            border: 1px solid var(--p-border);
            box-shadow: var(--p-shadow);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .p-btn-upload:hover {
            border-color: var(--p-primary);
            color: var(--p-primary);
            box-shadow: 0 0 0 3px var(--p-primary-ring);
        }

        /* Inputs Spacing & Rhythm */
        .p-form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.75rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 640px) {
            .p-form-grid-2 {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        .p-field-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.75rem;
        }

        .p-field-group:last-child {
            margin-bottom: 0;
        }

        .p-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--p-text-main);
            margin-bottom: 0.625rem;
        }

        .p-label span.req {
            color: #ef4444;
            margin-left: 0.25rem;
        }

        .p-field-desc {
            font-size: 0.775rem;
            color: var(--p-text-muted);
            margin-top: 0.375rem;
        }

        .p-error {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #ef4444;
            margin-top: 0.4rem;
        }

        .p-form-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 1.25rem;
            border-top: 1px solid var(--p-border);
            margin-top: 1rem;
        }

        /* Left-aligned Container for Password & 2FA Tabs */
        .p-left-container {
            max-width: 980px;
            margin-left: 0;
            margin-right: auto;
            width: 100%;
            box-sizing: border-box;
        }

        .p-tips-box {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            background: var(--p-bg-subtle);
            border: 1px solid var(--p-border);
            margin-bottom: 1.75rem;
            display: flex;
            gap: 0.875rem;
            align-items: flex-start;
        }

        .p-tips-icon {
            color: var(--p-primary);
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .p-tips-content h5 {
            margin: 0 0 0.35rem 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--p-text-main);
        }

        .p-tips-list {
            margin: 0;
            padding-left: 1.25rem;
            font-size: 0.775rem;
            color: var(--p-text-muted);
            line-height: 1.5;
        }

        /* Password Visibility Toggle Button */
        .p-password-toggle-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            color: var(--p-text-muted);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .p-password-toggle-btn:hover {
            color: var(--p-text-main);
            background: var(--p-bg-subtle);
        }

        /* 2FA Tab Elements */
        .p-2fa-status-banner {
            border-radius: 1rem;
            border: 1px solid var(--p-border);
            padding: 1.5rem 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            transition: all 0.2s ease;
            box-shadow: var(--p-shadow);
        }

        @media (min-width: 640px) {
            .p-2fa-status-banner {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .p-2fa-status-banner.is-active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0.02) 100%);
            border-color: rgba(16, 185, 129, 0.35);
        }

        .p-2fa-status-banner.is-disabled {
            background: var(--p-bg-card);
            border-color: var(--p-border);
        }

        .p-2fa-left {
            display: flex;
            align-items: flex-start;
            gap: 1.125rem;
        }

        .p-2fa-status-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: var(--p-shadow);
        }

        .p-2fa-status-icon.active {
            background: #10b981;
            color: #ffffff;
        }

        .p-2fa-status-icon.inactive {
            background: var(--p-bg-subtle);
            color: var(--p-text-muted);
            border: 1px solid var(--p-border);
        }

        .p-2fa-title-wrap {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            flex-wrap: wrap;
        }

        .p-2fa-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--p-text-main);
        }

        .p-2fa-desc {
            margin: 0.35rem 0 0 0;
            font-size: 0.825rem;
            color: var(--p-text-muted);
            line-height: 1.45;
            max-width: 520px;
        }

        .p-qr-grid {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 2rem;
            align-items: start;
            margin-top: 1rem;
            width: 100%;
            box-sizing: border-box;
        }

        @media (max-width: 840px) {
            .p-qr-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        .p-qr-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: var(--p-shadow);
        }

        .dark .p-qr-card {
            background: #18181b;
            border-color: #27272a;
        }

        .p-qr-instruction {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            width: 100%;
        }

        .p-qr-instruction-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--p-text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            margin: 0;
            text-align: center;
        }

        .p-qr-instruction-text {
            font-size: 0.785rem;
            color: var(--p-text-muted);
            line-height: 1.45;
            margin: 0;
            text-align: center;
        }

        .p-qr-login-notice {
            margin-top: 0.25rem;
            padding: 0.65rem 0.85rem;
            border-radius: 0.625rem;
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.22);
            color: #047857;
            font-size: 0.775rem;
            line-height: 1.4;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            text-align: left;
        }

        .dark .p-qr-login-notice {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .p-secret-box {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-width: 0;
            width: 100%;
            box-sizing: border-box;
        }

        .p-secret-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--p-text-muted);
            margin-bottom: 0.5rem;
            display: block;
        }

        .p-secret-key-wrap {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .p-secret-code {
            flex: 1;
            min-width: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--p-primary);
            background: var(--p-bg-subtle);
            border: 1px solid var(--p-border);
            border-radius: 0.625rem;
            padding: 0.65rem 0.85rem;
            user-select: all;
            overflow-x: auto;
            white-space: nowrap;
            box-sizing: border-box;
            text-align: center;
        }

        .p-info-table {
            background: var(--p-bg-subtle);
            border: 1px solid var(--p-border);
            border-radius: 0.75rem;
            padding: 0.875rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            font-size: 0.8rem;
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .p-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            color: var(--p-text-muted);
            flex-wrap: wrap;
        }

        .p-info-row span.val {
            font-weight: 600;
            color: var(--p-text-main);
            word-break: break-word;
        }

        /* Confirmation Modal Styles */
        .p-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
            z-index: 99999;
        }

        .p-modal-card,
        .p-modal-dialog {
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(0, 0, 0, 0.08) !important;
            max-width: 500px;
            width: 100%;
            padding: 1.75rem;
            position: relative;
            box-sizing: border-box;
            z-index: 100000;
        }

        .dark .p-modal-card,
        .dark .p-modal-dialog {
            background: #18181b !important;
            border: 1px solid #3f3f46 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.08) !important;
        }

        .p-modal-close-btn {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            color: var(--p-text-muted);
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .p-modal-close-btn:hover {
            color: var(--p-text-main);
            background: var(--p-bg-subtle);
        }

        .p-modal-body {
            display: flex;
            align-items: flex-start;
            gap: 1.125rem;
            margin-bottom: 1.5rem;
        }

        .p-modal-icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 1rem;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .p-modal-content {
            flex: 1;
        }

        .p-modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827 !important;
            margin: 0 0 0.35rem 0;
            line-height: 1.35;
        }

        .dark .p-modal-title {
            color: #f9fafb !important;
        }

        .p-modal-desc {
            font-size: 0.875rem;
            color: #4b5563 !important;
            margin: 0;
            line-height: 1.55;
        }

        .dark .p-modal-desc {
            color: #a1a1aa !important;
        }

        .p-modal-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--p-border);
        }

        /* Modal Transitions */
        .p-modal-fade-enter {
            transition: opacity 0.2s ease-out;
        }

        .p-modal-fade-enter-start {
            opacity: 0;
        }

        .p-modal-fade-enter-end {
            opacity: 1;
        }

        .p-modal-fade-leave {
            transition: opacity 0.15s ease-in;
        }

        .p-modal-fade-leave-start {
            opacity: 1;
        }

        .p-modal-fade-leave-end {
            opacity: 0;
        }

        .p-modal-scale-enter {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .p-modal-scale-enter-start {
            opacity: 0;
            transform: scale(0.95) translateY(-8px);
        }

        .p-modal-scale-enter-end {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        .p-modal-scale-leave {
            transition: all 0.15s ease-in;
        }

        .p-modal-scale-leave-start {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        /* Sessions Tab Elements */
        .p-sessions-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .p-session-card {
            background: var(--p-bg-subtle);
            border: 1px solid var(--p-border);
            border-radius: 0.875rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }

        .p-session-card:hover {
            border-color: rgba(245, 158, 11, 0.4);
            box-shadow: var(--p-shadow);
        }

        .p-session-card.is-current {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.06) 0%, rgba(245, 158, 11, 0.015) 100%);
            border-color: rgba(245, 158, 11, 0.5);
            box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.25), var(--p-shadow);
        }

        .dark .p-session-card.is-current {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(251, 191, 36, 0.02) 100%);
            border-color: rgba(251, 191, 36, 0.45);
            box-shadow: 0 0 0 1px rgba(251, 191, 36, 0.25), var(--p-shadow);
        }

        .p-session-card.is-inactive {
            opacity: 0.72;
            background: var(--p-bg-card);
        }

        .p-session-main {
            display: flex;
            align-items: flex-start;
            gap: 1.125rem;
            flex: 1;
            min-width: 0;
        }

        .p-session-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--p-bg-card);
            border: 1px solid var(--p-border);
            color: var(--p-text-muted);
            box-shadow: var(--p-shadow);
            transition: all 0.2s ease;
        }

        .p-session-card.is-current .p-session-icon-wrap {
            background: #fef3c7;
            border-color: #fde68a;
            color: #d97706;
        }

        .dark .p-session-card.is-current .p-session-icon-wrap {
            background: rgba(251, 191, 36, 0.15);
            border-color: rgba(251, 191, 36, 0.3);
            color: #fbbf24;
        }

        .p-session-info {
            flex: 1;
            min-width: 0;
        }

        .p-session-header-line {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem 0.75rem;
            margin-bottom: 0.35rem;
        }

        .p-session-device-name {
            font-size: 0.975rem;
            font-weight: 600;
            color: var(--p-text-main);
            word-break: break-word;
        }

        .p-badge-current {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.175rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.725rem;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.35);
            white-space: nowrap;
        }

        .dark .p-badge-current {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.45);
        }

        .p-pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: p-pulse 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        @keyframes p-pulse {
            to {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
        }

        .p-session-id-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.7rem;
            padding: 0.15rem 0.55rem;
            border-radius: 0.375rem;
            background: var(--p-bg-card);
            border: 1px solid var(--p-border);
            color: var(--p-text-muted);
            white-space: nowrap;
        }

        .p-badge-status-inactive {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 500;
            background: rgba(107, 114, 128, 0.12);
            color: var(--p-text-muted);
            border: 1px solid var(--p-border);
        }

        .p-badge-status-active {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .dark .p-badge-status-active {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.4);
        }

        .p-session-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1.25rem;
            font-size: 0.8rem;
            color: var(--p-text-muted);
            margin-top: 0.45rem;
        }

        .p-session-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .p-session-meta-item svg {
            width: 15px;
            height: 15px;
            flex-shrink: 0;
            opacity: 0.85;
        }

        .p-session-actions {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .p-session-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .p-session-actions {
                width: 100%;
                justify-content: flex-end;
                margin-top: 0.5rem;
                padding-top: 0.75rem;
                border-top: 1px dashed var(--p-border);
            }
        }
    </style>

    <div class="p-container">
        {{-- Filament Tabs Navigation --}}
        <div class="p-tabs-wrap">
            <x-filament::tabs>
                <x-filament::tabs.item
                    :active="$activeTab === 'profile'"
                    wire:click="switchTab('profile')"
                    icon="heroicon-m-user">
                    Profile Details
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === 'password'"
                    wire:click="switchTab('password')"
                    icon="heroicon-m-key">
                    Password & Security
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === '2fa'"
                    wire:click="switchTab('2fa')"
                    icon="heroicon-m-shield-check"
                    :badge="$two_factor_enabled ? 'Active' : 'Disabled'"
                    :badge-color="$two_factor_enabled ? 'success' : 'gray'">
                    Two-Factor Auth
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === 'sessions'"
                    wire:click="switchTab('sessions')"
                    icon="heroicon-m-computer-desktop"
                    :badge="$this->userSessions->where('is_active', true)->count()"
                    :badge-color="$this->userSessions->where('is_active', true)->count() > 0 ? 'success' : 'gray'">
                    Sessions
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        {{-- TAB 1: Profile Details --}}
        @if($activeTab === 'profile')
        <div class="p-profile-grid">
            {{-- Left: User Profile Avatar Card --}}
            <div class="p-card p-avatar-card">
                <div class="p-avatar-wrap">
                    <div class="p-avatar-inner">
                        @if($image)
                        <img src="{{ $image->temporaryUrl() }}" alt="Avatar Preview" class="p-avatar-img">
                        @elseif($current_image)
                        <img src="{{ asset('storage/' . $current_image) }}" alt="{{ auth()->user()->name }}" class="p-avatar-img">
                        @else
                        <div class="p-avatar-initials">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        @endif

                        {{-- Livewire Upload Loading Overlay --}}
                        <div wire:loading wire:target="image" class="p-avatar-loading">
                            <x-filament::loading-indicator class="w-8 h-8 text-white" />
                        </div>
                    </div>
                </div>

                <h3 class="p-user-name">{{ auth()->user()->name }}</h3>
                <p class="p-user-email">{{ auth()->user()->email }}</p>

                <div class="p-pill-badges">
                    <span class="p-badge p-badge-role">
                        <x-filament::icon icon="heroicon-m-identification" class="w-3.5 h-3.5" />
                        <span>{{ auth()->user()->type?->label() ?? 'User' }}</span>
                    </span>

                    @if($two_factor_enabled)
                    <span class="p-badge p-badge-2fa">
                        <x-filament::icon icon="heroicon-m-shield-check" class="w-3.5 h-3.5" />
                        <span>2FA Active</span>
                    </span>
                    @endif
                </div>

                <div class="p-meta-divider"></div>

                <div class="p-meta-row">
                    <span>Member Since</span>
                    <span class="p-meta-val">{{ auth()->user()->created_at?->format('M Y') ?? 'N/A' }}</span>
                </div>
            </div>

            {{-- Right: Profile Information & Photo Management Form --}}
            <div class="p-card">
                <div class="p-card-header">
                    <h3 class="p-card-title">
                        <x-filament::icon icon="heroicon-o-user" class="w-5 h-5 text-primary-500" />
                        <span>Profile Information</span>
                    </h3>
                    <p class="p-card-desc">
                        Update your personal account credentials and profile avatar.
                    </p>
                </div>

                <form wire:submit="updateProfile">
                    {{-- Avatar Upload Section --}}
                    <div class="p-upload-box">
                        <div class="p-upload-info">
                            <div class="p-upload-thumb">
                                @if($image)
                                <img src="{{ $image->temporaryUrl() }}" alt="Preview">
                                @elseif($current_image)
                                <img src="{{ asset('storage/' . $current_image) }}" alt="Avatar">
                                @else
                                <div class="p-thumb-initials">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                @endif
                            </div>
                            <div class="p-upload-text">
                                <h4>Profile Avatar</h4>
                                <p>Supports JPG, PNG, GIF up to <strong>3MB</strong>. Auto-converted to <strong>WebP</strong>.</p>
                            </div>
                        </div>

                        <div class="p-upload-actions">
                            {{-- Trigger File Input Button --}}
                            <label for="avatar-input" class="p-btn-upload">
                                <x-filament::icon icon="heroicon-m-arrow-up-tray" class="w-4 h-4 text-primary-500" />
                                <span>Change Photo</span>
                                <input
                                    type="file"
                                    id="avatar-input"
                                    wire:model="image"
                                    accept="image/*"
                                    style="display: none;">
                            </label>

                            @if($current_image)
                            <x-filament::button
                                color="danger"
                                size="sm"
                                outlined
                                type="button"
                                icon="heroicon-m-trash"
                                wire:click="removeImage"
                                wire:confirm="Are you sure you want to remove your profile avatar?">
                                Remove
                            </x-filament::button>
                            @endif
                        </div>
                    </div>

                    @error('image')
                    <div class="p-error" style="margin-top: -1.25rem; margin-bottom: 1.5rem;">
                        <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-4 h-4" />
                        <span>{{ $message }}</span>
                    </div>
                    @enderror

                    {{-- Form Inputs Grid with Generous Spacing --}}
                    <div class="p-form-grid-2">
                        {{-- Full Name --}}
                        <div class="p-field-group">
                            <label for="name" class="p-label">
                                <span>Full Name <span class="req">*</span></span>
                            </label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    id="name"
                                    type="text"
                                    wire:model="name"
                                    placeholder="Your full name"
                                    required />
                            </x-filament::input.wrapper>
                            @error('name')
                            <div class="p-error">
                                <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-4 h-4" />
                                <span>{{ $message }}</span>
                            </div>
                            @enderror
                        </div>

                        {{-- Email Address --}}
                        <div class="p-field-group">
                            <label for="email" class="p-label">
                                <span>Email Address <span class="req">*</span></span>
                            </label>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    id="email"
                                    type="email"
                                    wire:model="email"
                                    placeholder="admin@crm.com"
                                    required />
                            </x-filament::input.wrapper>
                            @error('email')
                            <div class="p-error">
                                <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-4 h-4" />
                                <span>{{ $message }}</span>
                            </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Save Changes Button --}}
                    <div class="p-form-actions">
                        <x-filament::button type="submit" icon="heroicon-m-check" size="lg" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                            <span wire:loading wire:target="updateProfile">Saving Changes...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- TAB 2: Password & Security --}}
        @if($activeTab === 'password')
        <div class="p-left-container">
            <div class="p-card">
                <div class="p-card-header">
                    <h3 class="p-card-title">
                        <x-filament::icon icon="heroicon-o-lock-closed" class="w-5 h-5 text-primary-500" />
                        <span>Update Password</span>
                    </h3>
                    <p class="p-card-desc">
                        Ensure your account stays protected by choosing a strong, unique password.
                    </p>
                </div>

                <form wire:submit="updatePassword" x-data="{ showCurrentPassword: false, showNewPassword: false, showConfirmPassword: false }">
                    {{-- Current Password --}}
                    <div class="p-field-group">
                        <label for="current_password" class="p-label">
                            <span>Current Password <span class="req">*</span></span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="current_password"
                                type="password"
                                x-bind:type="showCurrentPassword ? 'text' : 'password'"
                                wire:model="current_password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••••" />
                            <x-slot:suffix>
                                <button
                                    type="button"
                                    x-on:click="showCurrentPassword = !showCurrentPassword"
                                    class="p-password-toggle-btn"
                                    tabindex="-1"
                                    :title="showCurrentPassword ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle password visibility">
                                    <x-filament::icon x-show="!showCurrentPassword" icon="heroicon-m-eye" class="w-5 h-5" />
                                    <x-filament::icon x-show="showCurrentPassword" x-cloak icon="heroicon-m-eye-slash" class="w-5 h-5" />
                                </button>
                            </x-slot:suffix>
                        </x-filament::input.wrapper>
                        @error('current_password')
                        <div class="p-error">
                            <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-4 h-4" />
                            <span>{{ $message }}</span>
                        </div>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="p-field-group">
                        <label for="password" class="p-label">
                            <span>New Password <span class="req">*</span></span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="password"
                                type="password"
                                x-bind:type="showNewPassword ? 'text' : 'password'"
                                wire:model="password"
                                required
                                autocomplete="new-password"
                                placeholder="Minimum 8 characters" />
                            <x-slot:suffix>
                                <button
                                    type="button"
                                    x-on:click="showNewPassword = !showNewPassword"
                                    class="p-password-toggle-btn"
                                    tabindex="-1"
                                    :title="showNewPassword ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle password visibility">
                                    <x-filament::icon x-show="!showNewPassword" icon="heroicon-m-eye" class="w-5 h-5" />
                                    <x-filament::icon x-show="showNewPassword" x-cloak icon="heroicon-m-eye-slash" class="w-5 h-5" />
                                </button>
                            </x-slot:suffix>
                        </x-filament::input.wrapper>
                        @error('password')
                        <div class="p-error">
                            <x-filament::icon icon="heroicon-m-exclamation-circle" class="w-4 h-4" />
                            <span>{{ $message }}</span>
                        </div>
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div class="p-field-group">
                        <label for="password_confirmation" class="p-label">
                            <span>Confirm New Password <span class="req">*</span></span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                id="password_confirmation"
                                type="password"
                                x-bind:type="showConfirmPassword ? 'text' : 'password'"
                                wire:model="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repeat new password" />
                            <x-slot:suffix>
                                <button
                                    type="button"
                                    x-on:click="showConfirmPassword = !showConfirmPassword"
                                    class="p-password-toggle-btn"
                                    tabindex="-1"
                                    :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle password visibility">
                                    <x-filament::icon x-show="!showConfirmPassword" icon="heroicon-m-eye" class="w-5 h-5" />
                                    <x-filament::icon x-show="showConfirmPassword" x-cloak icon="heroicon-m-eye-slash" class="w-5 h-5" />
                                </button>
                            </x-slot:suffix>
                        </x-filament::input.wrapper>
                    </div>

                    {{-- Password Guidelines Box --}}
                    <div class="p-tips-box">
                        <x-filament::icon icon="heroicon-o-shield-check" class="w-5 h-5 p-tips-icon" />
                        <div class="p-tips-content">
                            <h5>Password Recommendations</h5>
                            <ul class="p-tips-list">
                                <li>Use at least 8 characters with a mix of letters and numbers.</li>
                                <li>Include special symbols (@, #, $, %, etc.) for optimal security.</li>
                                <li>Avoid using the same password across multiple online accounts.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="p-form-actions">
                        <x-filament::button type="submit" icon="heroicon-m-key" size="lg" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                            <span wire:loading wire:target="updatePassword">Updating...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- TAB 3: Two-Factor Authentication --}}
        @if($activeTab === '2fa')
        <div class="p-left-container" x-data="{ showDisableModal: false }">
            <div style="display: flex; flex-direction: column; gap: 1.75rem;">
                {{-- Status Banner --}}
                <div @class([ 'p-2fa-status-banner' , 'is-active'=> $two_factor_enabled,
                    'is-disabled' => ! $two_factor_enabled,
                    ])>
                    <div class="p-2fa-left">
                        <div @class([ 'p-2fa-status-icon' , 'active'=> $two_factor_enabled,
                            'inactive' => ! $two_factor_enabled,
                            ])>
                            <x-filament::icon icon="heroicon-o-shield-check" class="w-6 h-6" />
                        </div>

                        <div>
                            <div class="p-2fa-title-wrap">
                                <h3 class="p-2fa-title">
                                    {{ $two_factor_enabled ? 'Two-Factor Authentication is Active' : 'Two-Factor Authentication is Disabled' }}
                                </h3>
                                <span @class([ 'p-badge' , 'p-badge-2fa'=> $two_factor_enabled,
                                    'p-badge-role' => ! $two_factor_enabled,
                                    ])>
                                    {{ $two_factor_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            <p class="p-2fa-desc">
                                {{ $two_factor_enabled
                                        ? 'Your account is secured with Google Authenticator OTP verification on every login attempt.'
                                        : 'Protect your account against unauthorized access by requiring a 6-digit verification code from Google Authenticator.'
                                    }}
                            </p>
                        </div>
                    </div>

                    <div>
                        @if($two_factor_enabled)
                        <x-filament::button
                            color="danger"
                            size="sm"
                            outlined
                            icon="heroicon-m-no-symbol"
                            type="button"
                            x-on:click="showDisableModal = true">
                            Disable 2FA
                        </x-filament::button>
                        @else
                        <x-filament::button
                            color="primary"
                            icon="heroicon-m-shield-check"
                            size="md"
                            wire:click="enableTwoFactor">
                            Enable 2FA
                        </x-filament::button>
                        @endif
                    </div>
                </div>

                {{-- Condition 1: 2FA is currently ENABLED -> Display current QR code and Key --}}
                @if($two_factor_enabled && $two_factor_secret)
                <div class="p-card">
                    <div class="p-card-header">
                        <h3 class="p-card-title">
                            <x-filament::icon icon="heroicon-o-qr-code" class="w-5 h-5 text-primary-500" />
                            <span>Current Authenticator QR Code</span>
                        </h3>
                        <p class="p-card-desc">
                            Scan this QR code or use the manual secret key if you wish to add your CRM account to another mobile device.
                        </p>
                    </div>

                    <div class="p-qr-grid">
                        {{-- QR Code Card --}}
                        <div class="p-qr-card">
                            <div style="background: #ffffff; padding: 0.65rem; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center;">
                                {!! $this->qrCodeSvg !!}
                            </div>
                            <div class="p-qr-instruction">
                                <p class="p-qr-instruction-title">
                                    <x-filament::icon icon="heroicon-m-qr-code" class="w-4 h-4 text-primary-500" />
                                    <span>Scan with Google Authenticator</span>
                                </p>
                                <p class="p-qr-instruction-text">
                                    Scan this QR code using the <strong>Google Authenticator</strong> app on your mobile device. If you don't have Google Authenticator installed yet, please install it from the <strong>Google Play</strong> store (Android) or <strong>App Store</strong> (iOS).
                                </p>
                                <div class="p-qr-login-notice">
                                    <x-filament::icon icon="heroicon-m-shield-check" class="w-4 h-4 text-emerald-500 shrink-0" style="margin-top: 0.1rem;" />
                                    <span><strong>Next login:</strong> Next time after you log in, you will be prompted to enter the 6-digit OTP verification code from the app.</span>
                                </div>
                            </div>
                        </div>

                        {{-- Secret Key & Information --}}
                        <div class="p-secret-box">
                            <div>
                                <span class="p-secret-label">Manual Entry Secret Key</span>
                                <div class="p-secret-key-wrap" x-data="{ copied: false }">
                                    <code class="p-secret-code">
                                        {{ $two_factor_secret }}
                                    </code>
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        icon="heroicon-m-clipboard-document"
                                        type="button"
                                        x-on:click="navigator.clipboard.writeText('{{ $two_factor_secret }}'); copied = true; setTimeout(() => copied = false, 2500)">
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" style="color: #10b981; font-weight: 700;" x-cloak>Copied!</span>
                                    </x-filament::button>
                                </div>
                            </div>

                            <div class="p-info-table">
                                <div class="p-info-row">
                                    <span>Algorithm:</span>
                                    <span class="val">TOTP (Time-based One-Time Password)</span>
                                </div>
                                <div class="p-info-row">
                                    <span>Account:</span>
                                    <span class="val">{{ auth()->user()->email }}</span>
                                </div>
                                <div class="p-info-row">
                                    <span>Issuer:</span>
                                    <span class="val">{{ config('app.name', 'WaveX CRM') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Nice Popup Modal: Disable 2FA Confirmation --}}
            <div
                x-show="showDisableModal"
                x-cloak
                x-transition:enter="p-modal-fade-enter"
                x-transition:enter-start="p-modal-fade-enter-start"
                x-transition:enter-end="p-modal-fade-enter-end"
                x-transition:leave="p-modal-fade-leave"
                x-transition:leave-start="p-modal-fade-leave-start"
                x-transition:leave-end="p-modal-fade-leave-end"
                class="p-modal-backdrop"
                x-on:click.self="showDisableModal = false"
                x-on:keydown.escape.window="showDisableModal = false">
                <div
                    class="p-modal-card"
                    x-show="showDisableModal"
                    x-transition:enter="p-modal-scale-enter"
                    x-transition:enter-start="p-modal-scale-enter-start"
                    x-transition:enter-end="p-modal-scale-enter-end"
                    x-transition:leave="p-modal-scale-leave"
                    x-transition:leave-start="p-modal-scale-leave-start"
                    x-transition:leave-end="p-modal-scale-leave-end">
                    {{-- Close Button --}}
                    <button
                        type="button"
                        class="p-modal-close-btn"
                        x-on:click="showDisableModal = false"
                        aria-label="Close">
                        <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                    </button>

                    <div class="p-modal-body">
                        <div class="p-modal-icon-wrap">
                            <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-7 h-7 text-danger-500" />
                        </div>

                        <div class="p-modal-content">
                            <h3 class="p-modal-title">Disable Two-Factor Authentication?</h3>
                            <p class="p-modal-desc">
                                Are you sure you want to disable 2FA? This will remove Google Authenticator protection from your account, and you will only need your password to log in.
                            </p>
                        </div>
                    </div>

                    <div class="p-modal-actions">
                        <x-filament::button
                            color="gray"
                            type="button"
                            size="md"
                            x-on:click="showDisableModal = false">
                            Cancel
                        </x-filament::button>

                        <x-filament::button
                            color="danger"
                            type="button"
                            size="md"
                            icon="heroicon-m-no-symbol"
                            wire:click="disableTwoFactor"
                            x-on:click="showDisableModal = false">
                            Yes, Disable 2FA
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- TAB 4: Sessions --}}
        @if($activeTab === 'sessions')
        <div class="p-left-container" x-data="{
            showLogoutModal: false,
            showLogoutOtherModal: false,
            sessionToLogout: null,
            isCurrentSession: false,
            sessionDeviceName: '',
            openLogoutModal(id, isCurrent, name) {
                this.sessionToLogout = id;
                this.isCurrentSession = isCurrent;
                this.sessionDeviceName = name;
                this.showLogoutModal = true;
            }
        }">
            <div class="p-card">
                <div class="p-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h4 class="p-card-title">
                            <x-filament::icon icon="heroicon-m-computer-desktop" class="w-5 h-5 text-amber-500" />
                            Active Browser & Device Sessions
                        </h4>
                        <p class="p-card-desc">
                            Manage and log out your active sessions across devices and browsers. All timestamps are displayed in Africa/Cairo time (AM/PM).
                        </p>
                    </div>

                    @if($this->userSessions->where('is_active', true)->where('session_identifier', '!=', $this->currentSessionIdentifier)->count() > 0)
                    <x-filament::button
                        color="danger"
                        size="sm"
                        outlined
                        icon="heroicon-m-arrow-right-on-rectangle"
                        type="button"
                        x-on:click="showLogoutOtherModal = true">
                        Log Out Other Sessions
                    </x-filament::button>
                    @endif
                </div>

                {{-- Timezone Notice Banner --}}
                <div class="p-tips-box">
                    <div class="p-tips-icon">
                        <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5" />
                    </div>
                    <div class="p-tips-content">
                        <h5>Security Notice</h5>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            <!-- If you notice any unrecognized session, log it out immediately. All timestamps are localized in <strong>Africa/Cairo</strong> timezone in 12-hour format with AM/PM. -->
                            If you notice any unrecognized session, log it out immediately.
                        </p>
                    </div>
                </div>

                {{-- Sessions List --}}
                <div class="p-sessions-list">
                    @forelse($this->userSessions as $session)
                    @php
                    $deviceType = strtolower($session->device_type ?? '');
                    $deviceIcon = match (true) {
                    str_contains($deviceType, 'mobile') || str_contains($deviceType, 'phone') => 'heroicon-o-device-phone-mobile',
                    str_contains($deviceType, 'tablet') || str_contains($deviceType, 'ipad') => 'heroicon-o-device-tablet',
                    default => 'heroicon-o-computer-desktop',
                    };
                    $isCurrent = ($session->session_identifier === $this->currentSessionIdentifier);
                    $deviceNameLabel = ($session->browser_name ?: 'Web Browser') . ' on ' . ($session->os_name ?: ($session->device_name ?: 'Unknown Device'));
                    @endphp

                    <div class="p-session-card {{ $isCurrent ? 'is-current' : '' }} {{ ! $session->is_active ? 'is-inactive' : '' }}">
                        <div class="p-session-main">
                            <div class="p-session-icon-wrap" title="{{ $session->device_type ?? 'Device' }}">
                                <x-filament::icon :icon="$deviceIcon" class="w-6 h-6" />
                            </div>

                            <div class="p-session-info">
                                <div class="p-session-header-line">
                                    <span class="p-session-device-name">
                                        {{ $session->browser_name ?: 'Web Browser' }} {{ $session->browser_version ? Str::before($session->browser_version, '.') : '' }} on {{ $session->os_name ?: ($session->device_name ?: 'Unknown Device') }} {{ $session->os_version ?: '' }}
                                    </span>

                                    @if($isCurrent)
                                    <span class="p-badge-current" title="This is your current session on this device">
                                        <span class="p-pulse-dot"></span>
                                        This Device (Current Session)
                                    </span>
                                    @endif

                                    <span class="p-session-id-pill" title="Unique Session Identifier: {{ $session->session_identifier }}">
                                        <x-filament::icon icon="heroicon-m-finger-print" class="w-3.5 h-3.5 opacity-70" />
                                        <span>ID:</span>
                                        <code>{{ Str::limit($session->session_identifier, 14, '...') }}</code>
                                    </span>

                                    @if($session->is_active && ! $isCurrent)
                                    <span class="p-badge-status-active">
                                        Active
                                    </span>
                                    @elseif(! $session->is_active)
                                    <span class="p-badge-status-inactive">
                                        Logged Out
                                    </span>
                                    @endif
                                </div>

                                <div class="p-session-meta">
                                    {{-- IP Address --}}
                                    <div class="p-session-meta-item" title="IP Address">
                                        <x-filament::icon icon="heroicon-m-globe-alt" class="text-gray-400" />
                                        <span>{{ $session->ip_address ?: 'Unknown IP' }}</span>
                                    </div>

                                    {{-- Location (City, Country) --}}
                                    <div class="p-session-meta-item" title="Location">
                                        <x-filament::icon icon="heroicon-m-map-pin" class="text-gray-400" />
                                        <span>{{ collect([$session->city, $session->country])->filter()->implode(', ') ?: 'Unknown Location' }}</span>
                                    </div>

                                    {{-- Login Time (Africa/Cairo AM/PM) --}}
                                    <div class="p-session-meta-item" title="Login Time (Africa/Cairo)">
                                        <x-filament::icon icon="heroicon-m-arrow-left-on-rectangle" class="text-gray-400" />
                                        <span>Logged In: <strong>{{ $this->formatCairoTime($session->login_at) }}</strong></span>
                                    </div>

                                    {{-- Last Activity Time (Africa/Cairo AM/PM) --}}
                                    <div class="p-session-meta-item" title="Last Activity Time (Africa/Cairo)">
                                        <x-filament::icon icon="heroicon-m-clock" class="text-gray-400" />
                                        <span>Last Active: <strong>{{ $this->formatCairoTime($session->last_activity_at) }}</strong></span>
                                    </div>

                                    {{-- Logout / Revoked Time if inactive --}}
                                    @if(! $session->is_active && ($session->logout_at || $session->revoked_at))
                                    <div class="p-session-meta-item text-danger-500" title="Logged Out Time (Africa/Cairo)">
                                        <x-filament::icon icon="heroicon-m-arrow-right-on-rectangle" class="text-danger-400" />
                                        <span>Logged Out: <strong>{{ $this->formatCairoTime($session->logout_at ?? $session->revoked_at) }}</strong></span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-session-actions">
                            @if($session->is_active)
                            @if($isCurrent)
                            <x-filament::button
                                color="danger"
                                size="sm"
                                icon="heroicon-m-arrow-right-on-rectangle"
                                type="button"
                                x-on:click="openLogoutModal({{ $session->id }}, true, '{{ addslashes($deviceNameLabel) }}')">
                                Log Out
                            </x-filament::button>
                            @else
                            <x-filament::button
                                color="gray"
                                size="sm"
                                icon="heroicon-m-arrow-right-on-rectangle"
                                type="button"
                                x-on:click="openLogoutModal({{ $session->id }}, false, '{{ addslashes($deviceNameLabel) }}')">
                                Log Out
                            </x-filament::button>
                            @endif
                            @else
                            <span class="p-badge-status-inactive">
                                Revoked
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12 px-4 rounded-xl border border-dashed border-gray-200 dark:border-zinc-700">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-3 text-gray-400">
                            <x-filament::icon icon="heroicon-o-computer-desktop" class="w-6 h-6" />
                        </div>
                        <h5 class="text-sm font-semibold text-gray-900 dark:text-zinc-100 mb-1">No session records found</h5>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                            Active session tracking will record your devices and browsers as you log in.
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Modal: Single Session Logout Confirmation --}}
            <div
                class="p-modal-backdrop"
                x-show="showLogoutModal"
                x-cloak
                x-transition:enter="p-modal-fade-enter"
                x-transition:enter-start="p-modal-fade-enter-start"
                x-transition:enter-end="p-modal-fade-enter-end"
                x-transition:leave="p-modal-fade-leave"
                x-transition:leave-start="p-modal-fade-leave-start"
                x-transition:leave-end="p-modal-fade-leave-end"
                x-on:click.self="showLogoutModal = false"
                x-on:keydown.escape.window="showLogoutModal = false">
                <div
                    class="p-modal-card"
                    x-show="showLogoutModal"
                    x-transition:enter="p-modal-scale-enter"
                    x-transition:enter-start="p-modal-scale-enter-start"
                    x-transition:enter-end="p-modal-scale-enter-end"
                    x-transition:leave="p-modal-scale-leave"
                    x-transition:leave-start="p-modal-scale-leave-start"
                    x-transition:leave-end="p-modal-scale-leave-end">
                    <button
                        type="button"
                        class="p-modal-close-btn"
                        x-on:click="showLogoutModal = false"
                        aria-label="Close">
                        <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                    </button>

                    <div class="p-modal-body">
                        <div class="p-modal-icon-wrap">
                            <x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="w-7 h-7 text-danger-500" />
                        </div>

                        <div class="p-modal-content">
                            <template x-if="isCurrentSession">
                                <div>
                                    <h3 class="p-modal-title">Log Out Current Session?</h3>
                                    <p class="p-modal-desc">
                                        Logging out of this session will immediately end your current login session on this device and redirect you to the login screen.
                                    </p>
                                </div>
                            </template>
                            <template x-if="!isCurrentSession">
                                <div>
                                    <h3 class="p-modal-title">Log Out Browser Session?</h3>
                                    <p class="p-modal-desc">
                                        Are you sure you want to log out and terminate access for <strong class="text-gray-900 dark:text-gray-100" x-text="sessionDeviceName"></strong>? Any user on that device will be logged out on their next request.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="p-modal-actions">
                        <x-filament::button
                            color="gray"
                            type="button"
                            size="md"
                            x-on:click="showLogoutModal = false">
                            Cancel
                        </x-filament::button>

                        <x-filament::button
                            color="danger"
                            type="button"
                            size="md"
                            icon="heroicon-m-arrow-right-on-rectangle"
                            x-on:click="$wire.logoutSession(sessionToLogout); showLogoutModal = false;">
                            <span x-text="isCurrentSession ? 'Log Out Now' : 'Terminate Session'"></span>
                        </x-filament::button>
                    </div>
                </div>
            </div>

            {{-- Modal: Logout All Other Sessions Confirmation --}}
            <div
                class="p-modal-backdrop"
                x-show="showLogoutOtherModal"
                x-cloak
                x-transition:enter="p-modal-fade-enter"
                x-transition:enter-start="p-modal-fade-enter-start"
                x-transition:enter-end="p-modal-fade-enter-end"
                x-transition:leave="p-modal-fade-leave"
                x-transition:leave-start="p-modal-fade-leave-start"
                x-transition:leave-end="p-modal-fade-leave-end"
                x-on:click.self="showLogoutOtherModal = false"
                x-on:keydown.escape.window="showLogoutOtherModal = false">
                <div
                    class="p-modal-card"
                    x-show="showLogoutOtherModal"
                    x-transition:enter="p-modal-scale-enter"
                    x-transition:enter-start="p-modal-scale-enter-start"
                    x-transition:enter-end="p-modal-scale-enter-end"
                    x-transition:leave="p-modal-scale-leave"
                    x-transition:leave-start="p-modal-scale-leave-start"
                    x-transition:leave-end="p-modal-scale-leave-end">
                    <button
                        type="button"
                        class="p-modal-close-btn"
                        x-on:click="showLogoutOtherModal = false"
                        aria-label="Close">
                        <x-filament::icon icon="heroicon-m-x-mark" class="w-5 h-5" />
                    </button>

                    <div class="p-modal-body">
                        <div class="p-modal-icon-wrap">
                            <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-7 h-7 text-danger-500" />
                        </div>

                        <div class="p-modal-content">
                            <h3 class="p-modal-title">Log Out All Other Sessions?</h3>
                            <p class="p-modal-desc">
                                Are you sure you want to terminate all other active browser sessions across all your devices? Your current session on this device will stay active and unaffected.
                            </p>
                        </div>
                    </div>

                    <div class="p-modal-actions">
                        <x-filament::button
                            color="gray"
                            type="button"
                            size="md"
                            x-on:click="showLogoutOtherModal = false">
                            Cancel
                        </x-filament::button>

                        <x-filament::button
                            color="danger"
                            type="button"
                            size="md"
                            icon="heroicon-m-arrow-right-on-rectangle"
                            wire:click="logoutOtherSessions"
                            x-on:click="showLogoutOtherModal = false">
                            Log Out All Other Devices
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>