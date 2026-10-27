# Mutamark ERP — CyberPanel Production Deployment Guide

**Domain:** `https://mutamark.wolinet.com/`  
**Repository:** `git@github.com:Rb-tech-byte/mutamark.git`  
**User Context:** `mutam9314` @ `dc.wolinet.com`  

---

## 1. Add Deploy Key to GitHub
1. Open your repository on GitHub: [`https://github.com/Rb-tech-byte/mutamark`](https://github.com/Rb-tech-byte/mutamark)
2. Go to **Settings** &rarr; **Deploy keys** &rarr; **Add deploy key**.
3. Title: `CyberPanel mutam9314@dc.wolinet.com`
4. Key: Paste your key:
   ```text
   ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQCrMhCpcRXatdu5+rrQuugYU8xryMx5hbBLwAm9KfbTLeiRTufirsabtXomv9G8cFmH9zmG7qWyXBJicerwpYkPqz1sL8QY/1wuWcVu0AjPBlMLOqA94OrqYpNW5sfHiWPDatcg8EzKFrEQdtZjxGD0qxiWE8wfitcPKGVvJpgDOyb+O3uNSqE9y9IO/PBbcflnF/xXJe849gmzitr4BoQnokKrF3fz3ogJ/ECyAa/O6WDvuOlmBNbdyu3Vo90SXLjY1uijnsr7CpKmYZOzyfIzjNniyhp9uaQcN4a5ifObaDeHYfifXGK/3VxB44FGkfIHsBDXyhaZCvscNuPsBqDpaNBRBUfU64jhpEOIIHIclypAwFBucPfcrWXWgcw27ML9RK5OHmhGyoiLvY+7IpJvgfaj1n84Eit01t6s4dFYBlkzah5hijTsMJyvL0Qb2FJuDIKT0bwR1tnFqnTtUo8JkY2NtkTxj5G+SMBd8jk2W9a4zF91DMSxb3svrDDUFUk= mutam9314@dc.wolinet.com
   ```
5. Check **Allow write access** (optional, recommended for tags/releases) &rarr; Click **Add key**.

---

## 2. Connect Repository in CyberPanel
1. In CyberPanel, go to **Websites** &rarr; **List Websites** &rarr; manage `mutamark.wolinet.com`.
2. Under **Application Manager** or **Git**:
   * Git URL: `git@github.com:Rb-tech-byte/mutamark.git`
   * Branch: `main`
3. Click **Pull / Clone**.

---

## 3. Run Initial Setup on the Server
Connect via SSH or CyberPanel Web Terminal and run:

```bash
cd /home/mutamark.wolinet.com/public_html
bash deploy/setup_cyberpanel.sh
```

This automated script will:
* Set correct permissions (`chmod 644 .env`, `chmod -R 775 storage bootstrap/cache public`).
* Create `storage/installed` to mark the ERP as installed.
* Generate `APP_KEY` if not present.
* Install optimized production composer dependencies (`composer install --no-dev`).
* Run migrations (`php artisan migrate --force`).
* Link public storage (`php artisan storage:link`).
* Cache configs, routes, and views (`php artisan config:cache`, etc.).
* Restart OpenLiteSpeed / PHP worker.

---

## 4. Continuous Deployment (Automatic Updates)

### Option A: CyberPanel Webhook (Instant on Git Push)
1. In CyberPanel Git tab, copy the **Webhook URL**.
2. On GitHub repository &rarr; **Settings** &rarr; **Webhooks** &rarr; **Add webhook**:
   * Payload URL: paste CyberPanel Webhook URL.
   * Content type: `application/json`
   * Events: `Just the push event`.
3. Every `git push origin main` will automatically pull and update!

### Option B: GitHub Actions Workflow
The repository includes [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml).
Add secrets in **GitHub** &rarr; **Settings** &rarr; **Secrets and variables** &rarr; **Actions**:
* `CYBERPANEL_HOST`: your server IP (or `dc.wolinet.com`)
* `CYBERPANEL_USER`: `mutam9314`
* `CYBERPANEL_SSH_KEY`: your private key
