# Deployment

## Architecture

Deploy the React/Vite frontend to Vercel. Laravel is a separate PHP API and must run on a PHP/container host; Vercel does not natively run this Laravel application. The backend also needs a managed MySQL database, MongoDB Atlas for the assistant/vector features, and durable object storage if users upload documents.

## Vercel frontend

1. Import this repository into Vercel and set **Root Directory** to `Dunki` (the subdirectory containing `package.json` and `vercel.json`). `vercel.json` builds the frontend workspace and serves the React Router app for direct page loads.
2. Set `VITE_API_URL` in the Vercel project environment variables to the backend API URL, including `/api`, for example `https://api.example.com/api`. Set it for Production and Preview as appropriate.
3. Redeploy after setting the variable. Vite embeds it at build time.

## Laravel API

Deploy `backend/` using its Dockerfile on a PHP/container host. Configure production environment variables there; do not commit a `.env` file or secrets. At minimum configure:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` (generate with `php artisan key:generate --show`), and `APP_URL`.
- `DB_CONNECTION=mysql` and the managed MySQL host, port, database, username, and password.
- CORS allows all browser origins. The API still requires authentication for protected routes; this project uses bearer tokens and does not rely on credentialed cookies.
- `MONGODB_URI`, `MONGODB_DATABASE`, `MONGODB_COLLECTION`, and `MONGODB_VECTOR_INDEX` for MongoDB Atlas features.
- `GEMINI_API_KEY` if enabling Gemini-backed features.

Run `php artisan migrate --force` against the production database as a release/deploy step. Use a persistent or object-storage-backed filesystem for uploaded documents; the container's local filesystem is not durable. Configure production cache, session, and queue drivers to match the services available on the selected host.

## Local checks

From the repository root, run `npm ci`, `npm run build`, and `npm run lint`. Backend tests require Composer dependencies, a configured test database, and the PHP MongoDB extension if MongoDB-backed code is loaded; run `composer install` in `backend/` and then `php artisan test` there.