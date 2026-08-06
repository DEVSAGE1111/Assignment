# Engineering Decisions & Architectural Choices

This document outlines the key engineering decisions, structural designs, and architectural trade-offs made during the development of Formify AI.

---

## 1. Database Architecture: JSON vs. Schema-per-Form
**Decision**: Store form schemas (field definitions) and submissions response data in JSON columns (`json` data type in MySQL).

### Rationale:
- **Flexibility**: Traditional database setups might create a new database table for each form. While this is highly structured, it requires running dynamic migrations in production, altering databases on-the-fly, and managing hundreds of tables, which does not scale well in multi-tenant environments.
- **Relational Integrity**: By using JSON columns, we keep our database schema fixed with just two main tables (`forms` and `submissions`).
- **Query Performance**: Modern MySQL (and SQLite) natively supports indexing and querying JSON values, meaning search and retrieval operations are extremely fast.
- **Scalability**: Submissions are tied directly to form records through clean, indexed foreign keys, ensuring fast cascades and clean database garbage collection.

---

## 2. Zero-Dependency Excel & Word Document Parsing
**Decision**: Implement a custom, pure-PHP file parser (`DocumentParser`) using PHP's native `ZipArchive` and `SimpleXML` instead of installing third-party packages like `phpoffice/phpword` or `maatwebsite/excel`.

### Rationale:
- **No Dependency Bloat**: Heavy Office libraries frequently pull in dozens of sub-dependencies, expanding the `vendor/` folder, adding memory overhead, and risking dependency/composer version conflicts.
- **Office Formats are XML**: Both `.xlsx` and `.docx` files are simply compressed ZIP archives containing structured XML files (`xl/worksheets/sheet1.xml`, `word/document.xml`).
- **Speed & Control**: Extracting text directly from the underlying XML files using native PHP features operates at C-speed, is memory-safe, and provides full control over parsing without any extra baggage.
- **Safety**: Conforms to the codebase's strict guidelines of not changing application dependencies without explicit approval.

---

## 3. Smart Mock AI Fallback Mode
**Decision**: Design a dual-mode AI Generation Service (`AiFormGenerator`) that automatically detects API keys (`GEMINI_API_KEY`, `OPENAI_API_KEY`) and falls back to a smart, keyword-based mock generator if neither is configured.

### Rationale:
- **Frictionless Onboarding**: Assessment reviewers should be able to run `php artisan migrate --seed` and immediately test prompt-based generation without registering for cloud services or exposing their personal API keys.
- **High-Fidelity Mocks**: The mock engine analyzes user prompts for keywords like "feedback", "contact", "registration", or "newsletter" to return custom, highly relevant, and realistic form layouts instead of generic placeholder text.

---

## 4. API Security: Custom Token Header Middleware
**Decision**: Secure REST APIs using custom header-matching middleware (`VerifyApiToken`) checking for `X-API-Key`.

### Rationale:
- **Lightweight Access**: Since forms retrieve submission tables programmatically, developers need a simple token header verification to ingest data into CRMs or BI tools.
- **Reduced Complexity**: Using Laravel Sanctum or Passport for machine-to-machine form ingestion would introduce heavy session tables and bearer token lookup overhead. A simple header check is secure, fast, and easy to configure via `.env`.

---

## 5. Livewire 4 Class-Based Architecture
**Decision**: Utilize Class-based Livewire components (`app/Livewire/`) rather than Single File Components (SFC) for main app screens.

### Rationale:
- **Separation of Concerns**: Main dashboard screens, form builder logic, and submissions lists manage complex processing (API calls, file uploads, file parsing, dynamic validation arrays). Keeping the PHP code in dedicated classes separate from Blade markup ensures the codebase remains readable, modular, and easy to read.
- **Testability**: Class-based structures integrate perfectly with Laravel's feature testing tools and allow unit tests to mock and assert component states cleanly.
