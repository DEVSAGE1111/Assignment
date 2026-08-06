# Formify AI - AI-Powered Form Builder

Formify AI is a premium, high-fidelity AI-powered form builder application built on **Laravel 13**, **Livewire 4**, **Tailwind CSS v4**, and **MySQL**. It enables users to dynamically generate, edit, share, and analyze forms, import configurations from Word/Excel, bulk upload submissions, and securely integrate forms with external services using REST APIs.

---

## ⚡ Features

### 1. Multi-Mode Form Creator
- **Manual Drag-and-Drop Builder**: Interactively add, delete, customize, and reorder fields (text, number, email, select, radio, checkbox, textarea).
- **AI-Powered Form Generation**: Enter a natural language prompt (e.g., "Create a job recruitment form"), and let AI generate the form structure, titles, descriptions, and validations dynamically.
- **Document Schema Import**: Upload an Excel (.xlsx) or CSV (.csv) file outlining your questions, types, and placeholders to generate forms in bulk instantly.
- **AI Document Context Import**: Upload a Word (.docx) file containing arbitrary text or questions, and let AI analyze the document to build a structured questionnaire automatically.

### 2. Form Submission & Statistics Dashboard
- **Dashboard**: Track forms created, response counts, and recent submissions.
- **Public Form Viewer**: Share unique public URLs (`/forms/{uuid}`) for users to submit responses with dynamic schema-based validation.
- **CSV Data Export**: Download submissions with a single click in a standardized spreadsheet format.
- **Response Spreadsheet Import**: Upload bulk responses from an Excel/CSV file to populate your submissions database automatically.
- **AI Word Response Import**: Upload a filled Word (.docx) questionnaire, and let AI parse the filled answers into structured submission records.

### 3. Secure REST APIs & Developer Tools
- **GET `/api/forms/{uuid}`**: Retrieve a form's JSON schema (labels, types, placeholders, options).
- **POST `/api/forms/{uuid}/submit`**: Post validated responses from external frontends.
- **GET `/api/forms/{uuid}/submissions`**: Retrieve submissions securely. Protected via API keys (`X-API-Key` headers).
- **Interactive Developer Guide**: Built-in documentation `/api-docs` displaying integration examples for Guzzle PHP, JavaScript, and cURL.

### 4. Dynamic AI Settings Configuration (Database-Backed)
- **Settings Panel `/ai-settings`**: Configure API credentials and active models for Google Gemini, OpenAI, xAI (Grok), and Ollama dynamically through the UI.
- **Live Connection Pinger**: Click "Test Connection" to ping credentials against provider endpoints in real-time.
- **Dynamic Model Fetcher**: Automatically retrieve available models directly from provider endpoints (e.g., fetching available container models from Ollama or `gemini-*` parameters from Google).

---

## 🔒 Security & Authentication

All administrative pages, creators, submission analytics, and settings are fully protected behind a global login wall. 
*   **Login Route**: `/login`
*   **Default Seeded Admin Credentials**:
    *   **Email**: `admin@example.com`
    *   **Password**: `password`

---

## ⚙️ Installation & Setup

Ensure you have **PHP 8.3+**, **MySQL**, and **Composer** installed.

### 1. Clone & Install Dependencies
```bash
composer install
npm install
```

### 2. Configure Database & Environment
1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
2. Configure your database settings. Create a MySQL database named `form_builder` and update `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=form_builder
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Set your **Secure REST API Token** in `.env`:
   ```env
   API_SECRET_TOKEN=formify_secret_token_12345
   ```

### 3. Build & Run Application
1. Run migrations and database seeders (this automatically creates the Admin user and initial settings profile):
   ```bash
   php artisan migrate:fresh --seed
   ```
2. Compile Vite assets for production:
   ```bash
   npm run build
   ```
3. Start the Laravel development server:
   ```bash
   php artisan serve
   ```
4. Access the application in your browser at [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 🧪 Testing

The project uses the **Pest PHP** testing framework. To verify endpoints, models, validations, redirects, and AI services, run:
```bash
php artisan test
```

---

## 📁 Sample Import Files
We provide downloadable templates inside the application to make testing seamless:
- **Form Structure Import**: Download [`public/downloads/form_template.csv`](file:///c:/xampp/htdocs/asg/assg/public/downloads/form_template.csv) to preview the import structure.
- **Form Response Bulk Import**: Download [`public/downloads/response_import_sample.csv`](file:///c:/xampp/htdocs/asg/assg/public/downloads/response_import_sample.csv) to preview the bulk submissions structure.
- **Word Prompt Import**: Download [`public/downloads/sample_form_prompt.docx`](file:///c:/xampp/htdocs/asg/assg/public/downloads/sample_form_prompt.docx) to preview the Word document AI prompt parsing.
