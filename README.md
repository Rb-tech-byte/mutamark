# Mutamark ERP

**Mutamark ERP** is an enterprise-grade ERP system built for modern businesses, powered by **Laravel 12**, **Inertia.js**, **React**, **TypeScript**, and **Tailwind CSS**.

- **Production Domain:** [https://mutamark.wolinet.com/](https://mutamark.wolinet.com/)
- **Technology Stack:** Laravel 12 + Inertia.js 2.0 + React 18 + MySQL + LiteSpeed / OpenLiteSpeed
- **Architected & Maintained by:** Wolinet Technology Ltd

---

## Key Modules
- **Sales & Orders Management:** Quotes, invoices, order fulfillment, and client billing.
- **Inventory & Warehouse Tracking:** Real-time stock valuation, multi-warehouse support, barcode scanning.
- **Finance & Accounting:** General ledger, chart of accounts, automated tax reporting (ZATCA compliant).
- **Human Resources (HRM):** Employee records, payroll calculation, attendance and leave management.
- **Client & Vendor Portals:** Granular role-based access control via Spatie Permissions.
- **Two-Factor Authentication (2FA):** Google Authenticator / TOTP support.

---

## Production Deployment on CyberPanel

### Automated Setup
On your CyberPanel server (`/home/mutamark.wolinet.com/public_html`):
```bash
# 1. Clone repository
git clone git@github.com:Rb-tech-byte/mutamark.git .

# 2. Run initial setup script
bash deploy/setup_cyberpanel.sh
```

### Continuous Deployment (CD)
Whenever new commits are pushed to the `main` branch:
- **CyberPanel Webhook:** Automatically triggers `git pull` and reloads caches.
- **Manual / SSH Deployment:**
  ```bash
  bash deploy/deploy.sh
  ```
- Detailed deployment instructions are located in [`deploy/CYBERPANEL_INSTRUCTIONS.md`](deploy/CYBERPANEL_INSTRUCTIONS.md).

---

## Security
- **PHP2Shell Mitigation:** `session.upload_progress.enabled = Off` and `expose_php = Off`.
- **Sensitive File Protection:** Root `.htaccess` denies access to `.env`, `composer.json`, `package.json`, and database dump files.
- **Reverse Proxy Support:** Pre-configured `trustProxies(at: '*')` for SSL termination on LiteSpeed / Cloudflare.

---

## License
Proprietary & Confidential &mdash; Wolinet Technology Ltd. All Rights Reserved.
