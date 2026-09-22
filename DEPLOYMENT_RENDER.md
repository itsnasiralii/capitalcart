# 🚀 Deploying CapitalCart.pk to Render.com

This guide provides step-by-step instructions to deploy CapitalCart.pk to **[Render.com](https://render.com)** using the included automated Docker container and managed PostgreSQL database.

---

## 📋 Prerequisites
1. A free account on [GitHub](https://github.com).
2. A free account on [Render.com](https://render.com).

---

## 🛠️ Step 1: Push Code to GitHub

Open terminal in the project directory:

```bash
git add .
git commit -m "Production ready for Render.com and developer portfolio added"
git branch -M master
git remote add origin https://github.com/YOUR_USERNAME/capitalcart.git
git push -u origin master
```

*(If you already have a remote repo, simply run `git push origin master`)*

---

## ⚡ Step 2: Deploy with 1-Click Blueprint on Render

1. Log in to your **[Render.com Dashboard](https://dashboard.render.com)**.
2. Click **New +** (top right) and select **Blueprint**.
3. Connect your GitHub repository (`capitalcart`).
4. Render will automatically detect the **`render.yaml`** file and set up:
   - **Web Service**: Multi-stage Docker container (PHP 8.3 + Nginx + Livewire + Vite).
   - **Database**: Free Managed PostgreSQL Database (`capitalcart-db`).
5. Click **Apply**.
6. Render will automatically build the assets, migrate the database, and launch your live website!

---

## 🔑 Step 3: Admin Seeding on Render (One-time)

Once deployment finishes, open your web service on Render, go to the **Shell** tab, and run:

```bash
php artisan db:seed
```

This will create the default admin account:
- **Email**: `nasirali@capitalcart.pk`
- **Password**: `NasirAli@123`

---

## 🌐 Custom Domain & Free SSL
Render automatically provisions a free SSL certificate (`https://your-app.onrender.com`).
To use your custom domain (e.g. `capitalcart.pk`):
1. Go to **Settings** &rarr; **Custom Domains** on Render.
2. Add `capitalcart.pk` and `www.capitalcart.pk`.
3. Point your DNS records (CNAME or A record) as instructed by Render.
