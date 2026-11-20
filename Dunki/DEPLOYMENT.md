# Deployment

## Architecture

Deploy the React/Vite frontend to Vercel. Laravel is a separate PHP API and must run on a PHP/container host; Vercel does not natively run this Laravel application. The backend also needs a managed MySQL database, MongoDB Atlas for the assistant/vector features, and durable object storage if users upload documents.

## Vercel frontend

1. Import this repository into Vercel and set **Root Directory** to `Dunki` (the subdirectory containing `package.json` and `vercel.json`). `vercel.json` builds the frontend workspace and serves the React Router app for direct page loads.
2. Set `VITE_API_URL` in the Vercel project environment variables to the backend API URL, including `/api`, for example `https://api.example.com/api`. Set it for Production and Preview as appropriate.
3. Redeploy after setting the variable. Vite embeds it at build time.

## Laravel API on Railway

1. In Railway, create a service from this GitHub repository. The Git repository root contains the `Dunki/` folder, so set the service **Root Directory** to `/Dunki/backend` (not `/backend` or `/Dunki`). Set the service **Builder** to **Dockerfile** and the Dockerfile path to `/Dunki/backend/Dockerfile` if Railway does not detect it automatically. This makes the build context contain `artisan`, `composer.json`, and the Laravel source instead of the monorepo wrapper.
2. Add a MySQL service. In the Laravel service, configure `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY`, `APP_URL`, and `DB_CONNECTION=mysql`. Map `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to the MySQL service's connection variables. Do not commit a `.env` file or secrets.
3. Generate an application key with `php artisan key:generate --show` in a local backend install and set the returned value as Railway's `APP_KEY`. The command requires Composer dependencies to be installed.
4. Set the Railway health check path to `/up`, then generate a public domain for the Laravel service. The Dockerfile listens on Railway's injected `PORT`.
5. Add a Railway pre-deploy command `php artisan migrate --force` after the database variables are connected. This runs migrations before a new application deployment starts.
6. Set Vercel's `VITE_API_URL` to the Railway public URL ending in `/api`, then redeploy the frontend.

If the build log still says Railpack analyzed `./` and only found `.github/` and `Dunki/`, that deployment did not use the updated service settings. Trigger a new deployment after saving the root directory and Dockerfile builder; restarting an old deployment will not change its build context.

The API also needs MongoDB Atlas settings for assistant/vector features:

- `MONGODB_URI`, `MONGODB_DATABASE`, `MONGODB_COLLECTION`, and `MONGODB_VECTOR_INDEX` for MongoDB Atlas features.
- `GEMINI_API_KEY` if enabling Gemini-backed features.

CORS is configured to allow all browser origins. The API still requires authentication for protected routes; this project uses bearer tokens and does not rely on credentialed cookies. Use object storage for uploaded documents because the Railway container filesystem is ephemeral. Configure production cache, session, and queue drivers to use the available services; the defaults in this app use the database.

## Local checks

From the repository root, run `npm ci`, `npm run build`, and `npm run lint`. Backend tests require Composer dependencies, a configured test database, and the PHP MongoDB extension if MongoDB-backed code is loaded; run `composer install` in `backend/` and then `php artisan test` there.